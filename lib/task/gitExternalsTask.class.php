<?php
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
    switch($arguments['command'])
    {
      case 'pull' : $this->pullExternals();break;
      case 'push' : $this->pushExternals();break;
      default: throw new Exception(sprintf('[%s] is not a valid command [pull|push]'));break;
    }
  }
  
  protected function pullExternals()
  {
    $this->grepExternals();
  }
  
  protected function pushExternals()
  {
    $this->grepExternals();
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
        
        $this->externals[$external[0]] = array('local_location'=>$path,'git_location'=>$external[1]);
      }
    }
    
    var_dump($this->externals);
  }
}
