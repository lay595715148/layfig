<?php
$st = date('Y-m-d H:i:s').'.'.floor(microtime()*1000);
include_once __DIR__.'/layfig.php';
include_once __DIR__.'/lib/index.php';

Debugger::initialize(array(true, false));
Layfig::setter('actions.action-provider', 'TudingActionProvider');
Layfig::setter('actions.action-provider', 'TudingaActionProvider');
Debugger::debug(Layfig::getter('actions.action-provider'));
Layfig::setter('actions.action-provider', 'TudingbActionProvider', 'laybug');
Layfig::setter('actions.action-provider', 'TudingActionProvider', 'laybug');
Debugger::debug(Layfig::getter('actions.action-provider', 'laybug'));
$et = date('Y-m-d H:i:s').'.'.floor(microtime()*1000);
Debugger::debug(array($st,$et));
?>