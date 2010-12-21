<?php

/**
 * Simple Task for managing GIT Externals
 *
 * @link       https://github.com/digitalkaoz/rsGitExternalsPlugin
 * @version    1.0.0
 * @author     Robert Schönthal <seroscho at googlemail dot com>
 * @license    MIT License
 * @package    rsGitExternalsPlugin
 * @subpackage lib.task
 * @tutorial   https://github.com/digitalkaoz/rsGitExternalsPlugin/README.markdown
 */
class gitExternalsTask extends sfBaseTask
{
  protected $manager;
  
  protected function configure()
  {    
     $this->addArguments(array(
       new sfCommandArgument('command', sfCommandArgument::REQUIRED, 'pull,push,status'),
     ));

    $this->namespace        = 'plugin';
    $this->name             = 'git-externals';
    $this->briefDescription = 'manage git externals [pull,push,status]';
    $this->detailedDescription = <<<EOF
The [plugin:git-externals|INFO] task handles git externals.
  following commands are implemented:
    * [pull|INFO]   (pull all git repositories)
    * [push|INFO]   (push all git repositories)
    * [status|INFO] (check status of local repositories)
Call it with:

  [php symfony plugin:git-externals|INFO]
EOF;
  }

  /**
   * executes this task
   * arguments are: [pull|push|status]
   * everything more complex should be handled directly with git
   *
   * @param type $arguments
   * @param type $options 
   */
  protected function execute($arguments = array(), $options = array())
  {
    $manager = new GitExternalsManager($this->dispatcher,$this->formatter);
    $manager->grepExternals();    
    $manager->execute($arguments['command']);
  }  

}
