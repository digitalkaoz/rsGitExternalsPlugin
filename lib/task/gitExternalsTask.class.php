<?php

require_once dirname(__FILE__).'/../vendor/php-git-repo/lib/phpGitRepo.php';
require_once dirname(__FILE__).'/../vendor/php-git-repo/lib/phpGitRepoCommand.php';
/**
 * @author robert schoenthal
 */
class gitExternalsTask extends sfBaseTask
{
  protected $externals;
  
  protected function configure()
  {
    // // add your own arguments here
     $this->addArguments(array(
       new sfCommandArgument('command', sfCommandArgument::REQUIRED, 'pull or push'),
     ));

    // // add your own options here
    // $this->addOptions(array(
    //   new sfCommandOption('my_option', null, sfCommandOption::PARAMETER_REQUIRED, 'My option'),
    // ));

    $this->namespace        = 'plugin';
    $this->name             = 'git-externals';
    $this->briefDescription = 'manage git externals';
    $this->detailedDescription = <<<EOF
The [plugin:git-externals|INFO] task pushes or pulls remote git externals.
Call it with:

  [php symfony plugin:git-externals|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->grepExternals();
    
    switch($arguments['command'])
    {
      case 'pull' : $this->pullExternals();break;
      case 'push' : $this->pushExternals();break;
      default: throw new Exception(sprintf('[%s] is not a valid command [pull|push]'));break;
    }
  }
  
  protected function pullExternals()
  {
    foreach($this->externals as $name=>$external)
    {
      if(!is_dir($external['local']))
      {
        $this->logSection('git', sprintf('folder not found, cloning [%s]',$name));
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
          $repo = new phpGitRepo($external['local']);
          $repo->git('pull master');
        }
      }
    }
  }
  
  protected function pushExternals()
  {
  }
  
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
