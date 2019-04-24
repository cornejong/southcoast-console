<?php

namespace SouthCoast\Console;

use SouthCoast\Console\Console;
use SouthCoast\Helpers\ArrayHelper;
use SouthCoast\Helpers\StringHelper;

class Questionnaire
{
    const OPTION_LIST = 'option_list';

    /**
     * @var mixed
     */
    public static $lastAnswer = null;

    /**
     * @var array
     */
    protected $required_question_parameters = [
        'question', 'allow_empty',
        'type',
    ];

    /**
     * @param array $questions
     * @return mixed
     */
    public function __construct(array $questions = [])
    {
        foreach ($questions as $identifier => $question) {
            $this->addQuestion($identifier, $question);
        }

        return $this;
    }

    /**
     * @param string $identifier
     * @param array $question
     * @return mixed
     */
    public function addQuestion(string $identifier, array $question)
    {
        if (!ArrayHelper::requiredPramatersAreSet(($this->required_question_parameters), array_keys($question), $missing, true)) {
            throw new \Exception('Missing required parameters for question! Missing: ' . implode(', ', $missing), 1);
        }

        if (isset($question['checker']) && !is_callable($question['checker'])) {
            throw new \Exception('Non Callable Checker provided!', 1);
        }

        $this->questions[$identifier] = $question;

        return $this;
    }

    /**
     * @return mixed
     */
    public function ask()
    {
        $response = [];
        foreach ($this->questions as $identifier => $question) {
            $response[$identifier] = self::aksSingleQuestion($question, $response);
        }
        return $response;
    }

    /**
     * @param array $question
     * @param array $answers
     * @return mixed
     */
    public static function aksSingleQuestion(array $question, array $answers)
    {
        if (isset($question['type']) && $question['type'] == self::OPTION_LIST) {
            Console::log(print_r($question['options'], true));
        }

        $answer = Console::ask(self::formatQuestion($question));

        if (empty($answer) && !$question['allow_empty']) {
            Console::warning('Field can not be empty!');
            $answer = self::aksSingleQuestion($question, $answers);
        }

        $message = null;

        if (isset($question['checker']) && !$question['checker']($answer, $message, $answers)) {
            if (!empty($message)) {
                Console::warning($message);
            } else {
                Console::warning('Not an accepted answer!');
            }

            $answer = self::aksSingleQuestion($question, $answers);
        }

        self::$lastAnswer = $answer;

        return $answer;
    }

    /**
     * @param array $question
     * @return mixed
     */
    public static function formatQuestion(array $question)
    {
        $formatted = Color::blue(' ? ', false) . ' ' . $question['question'] . (isset($question['hint']) ? ' (' . $question['hint'] . ') : ' : ' : ');

        return $formatted;
    }

    /**
     * @param string $question
     */
    public static function quick(string $question)
    {
        return Console::ask(self::formatQuestion(['question' => $question]));
    }
}
