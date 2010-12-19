<?php

//load the git wrapper
require_once dirname(__FILE__).'/../vendor/php-git-repo/lib/phpGitRepo.php';
require_once dirname(__FILE__).'/../vendor/php-git-repo/lib/phpGitRepoCommand.php';

/**
 * Simple PHP wrapper for Git repository
 *
 * @link      https://github.com/digitalkaoz/rsGitExternalsPlugin
 * @version   1.0.0
 * @author    Robert Schönthal <seroscho at googlemail dot com>
 * @license   MIT License
 *
 * Documentation: https://github.com/digitalkaoz/rsGitExternalsPlugin/README.markdown
 * Tickets:       https://github.com/digitalkaoz/rsGitExternalsPlugin/issues
 */
class gitExternalsTask extends sfBaseTask
{
  protected $externals;
  
  protected function configure()
  {
     $this->addArguments(array(
       new sfCommandArgument('command', sfCommandArgument::REQUIRED, 'pull,push,commit,status'),
     ));

    $this->namespace        = 'plugin';
    $this->name             = 'git-externals';
    $this->briefDescription = 'manage git externals';
    $this->detailedDescription = <<<EOF
The [plugin:git-externals|INFO] task pushes or pulls remote git externals.
Call it with:

  [php symfony plugin:git-externals|INFO]
EOF;
  }

  /**
   * executes this task
   * arguments are: [pull|push|commit|status]
   * everything more complex should be handled directly with git
   *
   * @param type $arguments
   * @param type $options 
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->grepExternals();
    
    switch($arguments['command'])
    {
      case 'pull' : $this->pullExternals();break;
      case 'push' : $this->pushExternals();break;
      case 'commit' : $this->commitExternals();break;
      case 'status' : $this->statusExternals();break;
      default: throw new Exception(sprintf('[%s] is not a valid command [pull|push]'));break;
    }
  }

  /**
   * pull all git externals, clone if needed
   */
  protected function pullExternals()
  {
    foreach($this->externals as $name=>$external)
    {
      if(!is_dir($external['local']))
      {
        $this->logSection('git', sprintf('folder not found, cloning [%s]',$name));
        $this->doGitCommand($external['local'],'clone '.$external['git']);
      }
      else
      {
        if(file_exists($external['local'].DIRECTORY_SEPARATOR.'.svn'))
        {
          $this->logSection('git', sprintf('folder found but seems to be a svn repo [%s]',$name),null,'ERROR');
        }
        else
        {
          $this->logSection('git', sprintf('folder found, pulling [%s]',$name));
          $this->doGitCommand($external['local'],'pull origin ');
        }
      }
    }
  }
  
  /**
   * push all externals
   */
  protected function pushExternals()
  {
    foreach($this->externals as $name=>$external)
    {
      if(file_exists($external['local'].DIRECTORY_SEPARATOR.'.svn'))
      {
        $this->logSection('git', sprintf('folder found but seems to be a svn repo [%s]',$name),null,'ERROR');
      }
      else
      {
        $this->logSection('git', sprintf('folder found, pushing [%s]',$name));
        $this->doGitCommand($external['local'],'push origin ');
      }
    }
  }
  
  /**
   * check status of externals
   * @todo buggy
   */
  protected function statusExternals()
  {
    foreach($this->externals as $name=>$external)
    {
      if(file_exists($external['local'].DIRECTORY_SEPARATOR.'.svn'))
      {
        $this->logSection('git', sprintf('folder found but seems to be a svn repo [%s]',$name),null,'ERROR');
      }
      else
      {
        $this->logSection('git', sprintf('folder found, checking status [%s]',$name));
        //$this->doGitCommand($external['local'],'status');
      }
    }
  }
  
  /**
   * commit externals
   * @todo buggy
   */
  protected function commitExternals()
  {
    foreach($this->externals as $name=>$external)
    {
      if(file_exists($external['local'].DIRECTORY_SEPARATOR.'.svn'))
      {
        $this->logSection('git', sprintf('folder found but seems to be a svn repo [%s]',$name),null,'ERROR');
      }
      else
      {
        $this->logSection('git', sprintf('folder found, commiting [%s]',$name));
        //$this->doGitCommand($external['local'],'commit -a');
      }
    }    
  }
  
  /**
   * executes a git command (clone, pull,push,commit,status)
   * 
   * @param type $path
   * @param type $command 
   */
  protected  function doGitCommand($path,$command)
  {
    try
    {
      if(strpos($command, 'clone') === 0)
      {
        //clone
        mkdir($path);
        $repo = phpGitRepo::create($path);
        $repo->git('remote add origin '.substr($command,strpos($command, ' ')+1));
        $repo->git('pull origin master');
      }
      elseif(strpos($command, 'pull') === 0)
      {
        //pull
        $repo = new phpGitRepo($path);
        $repo->checkIsValidGitRepo();
        $repo->git($command.$repo->getCurrentBranch());
      }
      else
      {
        //status,pull,push
        $repo = new phpGitRepo($path);
        $repo->checkIsValidGitRepo();
        $repo->git($command);
      }
    }
    catch(Exception $e)
    {
      throw $e;
    }
  }

  /**
   * greps all externals defined in .gitexternals
   * use them like svn:externals
   */
  protected function grepExternals()
  {
    $finder = new sfFinder();
    $externals_files = $finder->
            type('file')->
            name('.gitexternals')->
            maxdepth(sfConfig::get('git_externals_depth',2))->
            in(sfConfig::get('sf_root_dir'));
    
    $externals = array();
    foreach ($externals_files as $file)
    {
      $externals = split("\n", file_get_contents($file));
      foreach($externals as $external)
      {
        $external = preg_split('/\s+/', $external);
        $path = substr($file, 0,  strripos($file, DIRECTORY_SEPARATOR)+1).$external[0];
        
        $this->externals[$external[0]] = array('local'=>$path,'git'=>$external[1]);
      }
    }
  }
}
