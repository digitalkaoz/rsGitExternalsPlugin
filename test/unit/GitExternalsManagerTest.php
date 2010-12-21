<?php

require_once dirname(__FILE__).'/../bootstrap/unit.php';
require_once dirname(__FILE__).'/../fixtures/mock-objects.php';

$t = new lime_test();

GitExternalsManager::$repoClass = 'testGitRepo';
$manager = new GitExternalsManager(new testEventDispatcher($t),new sfFormatter(80));


//@Test grepping externals
$t->info('grepping externals');
$t->is($manager->grepExternals(),3,'externals count correct');

//@Test commands 
$t->info('commands');
try{
  $manager->execute('foo');
  $t->fail('command foo not implemented');
}catch(Exception $e){
  $t->pass('command not implemented throws exception');
}

$t->is($manager->execute('pull'),true,'pulled successfully');
$t->is($manager->execute('push'),true,'pushed successfully');
$t->is($manager->execute('status'),true,'grepped status successfully');


//cleanup
rmdir(sfConfig::get('sf_root_dir').'/plugins/rsGitExternalsPlugin');