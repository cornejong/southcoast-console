<?php

namespace SouthCoast\Console;

class Process
{
    /**
     * @var string
     */
    protected $response = '';
    /**
     * @var mixed
     */
    protected $execution_path = null;
    /**
     * @var array
     */
    protected $environment = [];
    /**
     * @var mixed
     */
    protected $pipelines;
    /**
     * @var mixed
     */
    protected $process;
    /**
     * @var mixed
     */
    protected $command;
    /**
     * @var mixed
     */
    protected $logging_on = false;

    /**
     * @param array $config
     * @return mixed
     */
    public function __construct(array $config = null)
    {
        $this->pipe_config = [
            ["pipe", "r"], // stdin
            ["pipe", "w"], // stdout
            ["pipe", "w"], // stderr
        ];

        $this->logging_on = $config['logging'] ?? false;

        $this->log('Process Initialized!');

        return $this;
    }

    /**
     * @param string $message
     */
    protected function log(string $message)
    {
        if ($this->logging_on) {
            Console::log($message);
        }
    }

    /**
     * @return mixed
     */
    public function run()
    {
        $this->log('Setting up process...');

        $this->process = proc_open($this->command, $this->pipe_config, $pipelines, $this->getExecutionPath(), $this->environment);

        $this->log('Storing Pipelines...');
        $this->pipelines = $pipelines;

        $this->log('Checking for process resource...');
        if (is_resource($this->process)) {
            $this->log('Resource established...');
        } else {
            $this->close();
            throw new \Exception('Could not start process! Check call!', 1);
        }

        $this->log('Process Running...');

        return $this;
    }

    /**
     * @param $env
     * @return mixed
     */
    public function setEnvironment($env)
    {
        $this->log('Setting up environment...');

        $this->environment = $env;

        return $this;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function setExecutionPath($path)
    {
        $this->log('Setting up the execution path... (' . $path . ')');

        $this->execution_path = $path;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExecutionPath()
    {
        return $this->execution_path ?? Console::pwd();
    }

    /**
     * @param string $command
     * @return mixed
     */
    public function setCommand(string $command)
    {
        $this->log('Setting Command... (' . $command . ')');

        $this->command = $command;
        return $this;
    }

    /**
     * @param string $data
     */
    public function write(string $data)
    {
        $this->log('Writing data to process (' . $data . ')');

        /* Write the data to the process */
        return fwrite($this->pipelines[0], $data);
    }

    /**
     * @return mixed
     */
    public function read()
    {
        $this->log('Reading data from process...');

        /* Read the response */
        $this->response .= stream_get_contents($this->pipelines[1]);

        Console::log('Response: ' . $this->response);

        /* Return the response */
        return $this->response;
    }

    public function terminate()
    {
        $this->log('Terminating Pipelines...');

        /* close all the read/write pipes */
        foreach ($this->pipelines as $pipe) {
            fclose($pipe);
        }

        $this->log('Terminating Process...');
        /* Close the process and save the response code */
        $this->response_code = proc_terminate($this->process);
    }

    public function close()
    {
        $this->log('Closing Pipelines...');

        /* close all the read/write pipes */
        foreach ($this->pipelines as $pipe) {
            fclose($pipe);
        }

        $this->log('Closing Process...');
        /* Close the process and save the response code */
        $this->response_code = proc_close($this->process);
    }

    /**
     * @return mixed
     */
    public function response()
    {
        return $this->response;
    }

}
