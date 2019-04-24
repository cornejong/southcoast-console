<?php

namespace SouthCoast\Console;

use SouthCoast\Console\Console;
use SouthCoast\Helpers\ArrayHelper;
use SouthCoast\Helpers\Identifier;
use SouthCoast\Helpers\StringHelper;

class App
{
    const PATH_SEPERATOR_EXPRESSION = '(\<\[\~\]\>)';
    const PATH_SEPERATOR_OR_NOT_EXPRESSION = '(\<\[\~\]\>|)';
    const REQUIRED_VARIABLE_EXPRESSION = '([^(\<\[\~\]\>)]*)';
    const OPTIONAL_VARIABLE_EXPRESSION = '([^(\<\[\~\]\>)]*|)';
    const STRICT_VALUE_OPENER_EXPRESSION = '(';
    const STRICT_VALUE_CLOSER_EXPRESSION = ')';

    const MATCHING_PATTERN_CLOSER = '/';
    const MATCHING_PATTERN_OPENER = '/^';

    const VARIABLE_INDICATOR = '$';
    const OPTIONAL_INDICATOR = '*';

    const ARG_PATH_SEPARATOR = '<[~]>';

    /**
     * @var mixed
     */
    public $actions;
    /**
     * @var mixed
     */
    protected $version;
    /**
     * @var mixed
     */
    protected $name;
    /**
     * @var mixed
     */
    protected $description;

    /**
     * @var mixed
     */
    protected $case_insensitive = true;

    /**
     * @var array
     */
    protected $required_config_parameters = [
        'name', 'version', 'description',
    ];

    /**
     * @param array $config
     * @return mixed
     */
    public function __construct(array $config)
    {
        if (!ArrayHelper::requiredPramatersAreSet(($this->required_config_parameters), array_keys($config), $missing, true)) {
            throw new \Exception('Missing required config parameters! Missing: ' . implode(', ', $missing), 1);
        }

        $this->setName($config['name']);
        $this->setVersion($config['version']);
        $this->setDescription($config['description']);

        cli_set_process_title($this->getName());

        $this->addAction(['help'], 'Display this help menu', function ($object) {
            Console::log('Welcome to the ' . $this->name . ' help menu.');
            Console::log('This are the currently supported commands.');
            foreach ($this->actions as $action) {
                Console::log("\t[ " . implode(' ', $action['action']) . ' ] - ' . $action['description']);
            }
        }, '');

        return $this;
    }

    /**
     * @param array $commands
     * @param string $description
     * @param $handler
     * @param $handler_assets
     */
    public function addAction(array $commands, string $description, callable $handler, ...$handler_assets)
    {
        if (count($commands) < 1) {
            throw new \Exception('Minimal number of commands is 1, ' . count($commands) . ' provided!', 1);
        }

        /* Extract the variables from the provided route */
        $variables = self::extractActionVariableKeys($commands);

        $pattern = self::buildActionMatchPattern($commands, $variables);

        $identifier = Identifier::newGuid();

        $action = [
            'action' => $commands,
            'description' => $description,
            'pattern' => $pattern,
            'variables' => $variables,
            'handler' => $handler,
            'handler_assets' => $handler_assets ?? null,
        ];

        $this->actions[$identifier] = $action;
    }

    /**
     * @param array $action
     * @return mixed
     */
    public function addActionFromArray(array $action)
    {
        return $this->addAction($action['command'], $action['description'], $action['handler'], ...$action['handler_assets']);
    }

    /**
     * @param array $action
     * @return mixed
     */
    public function extractActionVariableKeys(array $action): array
    {
        $tmp = [
            'required' => [],
            'optional' => [],
        ];

        foreach ($action as $index => $item) {
            /* Check for variables */
            if (StringHelper::startsWith(self::VARIABLE_INDICATOR, $item)) {
                if (StringHelper::endsWith(self::OPTIONAL_INDICATOR, $item)) {
                    if ($index !== (count($action) - 1)) {
                        // throw new RoutesError(RoutesError::OPTIONAL_ROUTE_TOKEN_NOT_ON_END, implode('/', $action));
                    }

                    $tmp['optional'][] = $index;
                } else {
                    $tmp['required'][] = $index;
                }
            }
        }

        return $tmp;
    }

    /**
     * @param array $action
     * @param array $variables
     * @return mixed
     */
    public function buildActionMatchPattern(array $action, array $variables): string
    {
        $pattern = self::MATCHING_PATTERN_OPENER;

        foreach ($action as $index => $element) {
            if (in_array($index, $variables['required'])) {
                $pattern .= self::REQUIRED_VARIABLE_EXPRESSION;
            } elseif (in_array($index, $variables['optional'])) {
                $pattern .= self::PATH_SEPERATOR_OR_NOT_EXPRESSION . self::OPTIONAL_VARIABLE_EXPRESSION;
            } else {
                $pattern .= self::STRICT_VALUE_OPENER_EXPRESSION . $element . self::STRICT_VALUE_CLOSER_EXPRESSION;
            }

            if (in_array($index + 1, $variables['optional'])) {
                // $pattern .= self::PATH_SEPERATOR_OR_NOT_EXPRESSION;
            } elseif (in_array($index + 1, $variables['required'])) {
                $pattern .= self::PATH_SEPERATOR_EXPRESSION;
            } elseif ($index == (count($action) - 1)) {
                // $pattern .= self::PATH_SEPERATOR_EXPRESSION;
            } elseif ($index !== (count($action) - 1)) {
                $pattern .= self::PATH_SEPERATOR_EXPRESSION;
            }

        }

        $pattern .= self::MATCHING_PATTERN_CLOSER . ($this->case_insensitive ? 'i' : '');

        return $pattern;
    }

    /**
     * @param array $commands
     * @param $return_action
     */
    protected function actionIsKnown(array $commands, &$return_action)
    {
        $stringified = implode(self::ARG_PATH_SEPARATOR, $commands);

        foreach ($this->actions as $action) {
            if (preg_match($action['pattern'], $stringified)) {
                $return_action = $action;
                return true;
            }
        }

        return false;
    }

    public function run()
    {
        /* Check if there was any arg passed */
        if (!Console::envIsProvided()) {
            Console::error('No Arguments passed!');
            Console::exit();
        }

        $action = [];

        if (!$this->actionIsKnown(Console::env(), $action)) {
            Console::clear();
            Console::error('Action not known!');
            Console::exit();
        }

        Console::setEnvMap($action['action']);

        $handler = $action['handler'];

        $handler(...$action['handler_assets']);
    }

    /**
     * Get the value of version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the value of version
     *
     * @return  self
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
