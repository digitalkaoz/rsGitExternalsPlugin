<?php

//load the git wrapper
require_once dirname(__FILE__).'/vendor/php-git-repo/lib/phpGitRepo.php';
require_once dirname(__FILE__).'/vendor/php-git-repo/lib/phpGitRepoCommand.php';

/**
 * Simple PHP wrapper for Git repository
 *
 * @link       https://github.com/digitalkaoz/rsGitExternalsPlugin
 * @version    1.0.0
 * @author     Robert Schönthal <seroscho at googlemail dot com>
 * @license    MIT License
 * @package    rsGitExternalsPlugin
 * @subpackage lib
 * @tutorial   https://github.com/digitalkaoz/rsGitExternalsPlugin/README.markdown
 */
class GitExternalsManager
{
  protected $externals = array();
  protected $task, $dispatcher, $formatter;
  public static $repoClass = 'phpGitRepo';
  
  /**
   * constructor for injecting dispatcher and logger if needed
   * 
   * @param sfEventDispatcher $dispatcher
   * @param sfFormatter $formatter 
   */
  public function __construct(sfEventDispatcher $dispatcher = null, sfFormatter $formatter = null)
  {
    $this->dispatcher = $dispatcher;
    $this->formatter = $formatter;
  }
  
  /**
   * logs the git output
   * 
   * @param string $message
   * @param strin $priority
   */
  protected function log($message,$priority='INFO')
  {
    if($this->dispatcher && $this->formatter)
    {
      $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('git', $message, null, $priority))));
    }
  }

  /**
   * executes a command [pull,push,commit,status]
   * 
   * @param type $action
   * @param type $task
   * @return type 
   */
  public function execute($action,$task=null)
  {
    if(!$this->task)
    {
      $this->task = $task;
    }
    
    $fnName = strtolower($action).'Command';
    
    if(!method_exists($this, $fnName))
    {
      throw new Exception($action.' not implemented');
    }
    
    return array_walk($this->externals, array($this,$fnName));
  }
  
  /**
   * pulls a git external, clone if needed
   * 
   * @param $external the external config
   * @param $name the external name
   */
  protected function pullCommand($external, $name)
  {
    if(!is_dir($external['local']))
    {
      //clone
      $this->log(sprintf('folder not found, cloning [%s]',$name));
      $this->doGitCommand($external['local'],'clone '.$external['git']);
    }
    else
    {
      //pull
      $this->log(sprintf('folder found, pulling [%s]',$name));
      $this->doGitCommand($external['local'],'pull origin ');
    }
  }
  
  /**
   * push a git externals
   * 
   * @param $external the external config
   * @param $name the external name
   */
  protected function pushCommand($external, $name)
  {
    $this->log(sprintf('folder found, pushing [%s]',$name));
    $this->doGitCommand($external['local'],'push origin ');
  }
 
  /**
   * check status of externals
   * 
   * @param $external the external config
   * @param $name the external name
   */
  protected function statusCommand($external,$name)
  {
    $this->log(sprintf('folder found, checking status [%s]',$name));
    $this->doGitCommand($external['local'],'status');
  }
  
  /**
   * greps all externals defined in .gitexternals
   * use them like svn:externals
   * 
   * @return int number of externals
   */
  public function grepExternals()
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
      //extract all externals from the file
      foreach(file($file) as $external)
      {
        $external = preg_split('/\s+/', $external);
        $path = substr($file, 0,  strripos($file, DIRECTORY_SEPARATOR)+1).$external[0];
        
        if(!file_exists($path.DIRECTORY_SEPARATOR.'.svn'))
        {        
          $this->externals[$external[0]] = array('local'=>$path,'git'=>$external[1]);
        }
      }
    }
    
    return count($this->externals);
  }  
  
  /**
   * executes a git command (clone, pull,push,commit,status)
   * 
   * @param type $path
   * @param type $command 
   */
  protected function doGitCommand($path,$command)
  {
    $class = self::$repoClass;
    
    try
    {
      switch(substr($command, 0,  strpos($command, ' ') !==false ? strpos($command, ' ') : strlen($command)))
      {
        case 'clone' :
          mkdir($path);
          $repo = $class::create($path);
          $repo->git('remote add origin '.substr($command,strpos($command, ' ')+1));
          $repo->git('pull origin master');
          break;

        case 'pull' :
          $repo = new $class($path);
          $repo->checkIsValidGitRepo();
          $repo->git($command.$repo->getCurrentBranch());
          break;
        
        case 'status' :
          //activate debug for output
          $repo = new $class($path,true);
          $repo->checkIsValidGitRepo();
          $repo->git($command);
          break;
        
        default :
          $repo = new $class($path);
          $repo->checkIsValidGitRepo();
          $repo->git($command);
          break;
      }
    }
    catch(Exception $e)
    {
      $this->log($e->getMessage(),'ERROR');
    }
  }
  
}
