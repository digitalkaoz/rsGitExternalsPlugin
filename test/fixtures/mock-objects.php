<?php

require_once dirname(__FILE__).'/../../lib/vendor/php-git-repo/lib/phpGitRepo.php';
require_once dirname(__FILE__).'/../../lib/vendor/php-git-repo/lib/phpGitRepoCommand.php';

class testGitRepoCommand extends phpGitRepoCommand
{
  public function run()
  {
    return sprintf('cd %s && %s', escapeshellarg($this->dir), $this->commandString);
  }  
}
class testGitRepo extends phpGitRepo
{    
  protected static $defaultOptions = array(
      'command_class'   => 'testGitRepoCommand', // class used to create a command
      'git_executable'  => '/usr/bin/git'       // path of the executable on the server
  );
  
  public function __construct($dir, $debug = false, array $options = array())
  {
    $this->dir      = $dir;
    $this->debug    = $debug;
    $this->options  = array_merge(self::$defaultOptions, $options);

    $this->checkIsValidGitRepo();
  }

  public static function create($dir, $debug = false, array $options = array())
  {
    $options = array_merge(self::$defaultOptions, $options);
    $commandString = $options['git_executable'].' init';
    $command = new $options['command_class']($dir, $commandString, $debug);
    $command->run();

    $repo = new self($dir, $debug, $options);

    return $repo;
  }    
  
  public function checkIsValidGitRepo()
  {
    return true;
  }
  
}

class testEventDispatcher extends sfEventDispatcher
{
  protected $t = null;

  public function __construct($testObject)
  {
    $this->t = $testObject;
    $this->connect('command.log', array($this,'listenToGitLogEvent'));
  }

  public function listenToGitLogEvent(sfEvent $event)
  {
    foreach ($event->getParameters() as $key => $message)
    {
      if ('priority' === $key)
      {
        continue;
      }

      $this->t->diag(sprintf('%s', $message));
    }
  }  
}
