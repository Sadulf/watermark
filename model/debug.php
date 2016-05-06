<?php
/**
 * Debug module
 * You must call init($log_file,$log_sign) immediately after incuding this module
 * function init($log_file=__DIR__.'/log.txt',$log_sign='')
 * function addlog($text)
 * function a($text)
 * function fatal_error($to_screen,$to_log=null)
 */
namespace debug;

/**
 * initializes debugger
 * @param  string $log_file name of file for log
 * @param  string $log_sign some short text, that will be written before each error message
 * @return boolean    true if debugger is ready or false on error
 */
function init($log_file='log.txt',$log_sign=''){
	global $logfile,$logsign;
	$logsign = $log_sign;
	$logfile=fopen($log_file,'a');
	if($logfile===false)
		return false;
	set_error_handler('\debug\userErrorHandler',E_ALL);
	return true;
}

/**
 * Write text to log
 * @param  string $text Text to write
 * @return integer       Number of bytes written or false if error has occured
 */
function addlog($text){
	global $logfile,$logsign;
	if(is_null($text))
		return false;
	return fwrite($logfile,'['.date('Y.m.d H:i').'] '.$logsign.': '.$text."\r\n");
}

/**
 * Alias of addlog()
 * @param  string $text Text to write
 * @return integer       Number of bytes written or false if error has occured
 */
function a($text){
	return addlog($text);
}

/**
 * Displays an error message and terminates the script
 * @param  string $to_screen Text, that will be shown to user
 * @param  string $to_log    Text, that will be written to log
 * @return null
 */
function fatal_error($to_screen,$to_log=null){
	addlog($to_log);
	?><!DOCTYPE html>
	<html><head lang="en">
	<meta name="viewport" charset="utf-8">
	<title>Error!</title>
	</head><body><?php echo '<h3>Error has occured!</h3><p>'.$to_screen.'</p>'; ?>
	</body></html><?php
	die();
}


//============== INTERNAL FUNCTIONS =======================


function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars){
    $errortype = array (
                E_ERROR              => 'E_ERROR',
                E_WARNING            => 'E_WARNING',
                E_PARSE              => 'E_PARSE',
                E_NOTICE             => 'E_NOTICE',
                E_CORE_ERROR         => 'E_CORE_ERROR',
                E_CORE_WARNING       => 'E_CORE_WARNING',
                E_COMPILE_ERROR      => 'E_COMPILE_ERROR',
                E_COMPILE_WARNING    => 'E_COMPILE_WARNING',
                E_USER_ERROR         => 'E_USER_ERROR',
                E_USER_WARNING       => 'E_USER_WARNING',
                E_USER_NOTICE        => 'E_USER_NOTICE',
                E_STRICT             => 'E_STRICT',
                E_RECOVERABLE_ERROR  => 'E_RECOVERABLE_ERROR',
				E_DEPRECATED		 => 'E_DEPRECATED'
                );

	if(isset($errortype[$errno]))
		$erc=$errortype[$errno];
	else
		$erc='E_'.$errno;
	switch($erc){
		case 'E_ERROR':
			addlog($erc.': '.$errmsg.' in "'.$filename.':'.$linenum.'"');
			log_trace();
			return false;
		case 'E_NOTICE':
		case 'E_WARNING':
			addlog($erc.': '.$errmsg.' in "'.$filename.':'.$linenum.'"');
			log_trace();
			return true;
		case 'E_USER_ERROR':
			addlog($erc.': '.$errmsg.' в "'.$filename.':'.$linenum.'"');
			log_trace();
			fatal_error('We apologize for the inconvenience');
			break;
		case 'E_USER_WARNING':
			addlog($erc.': '.$errmsg.' в "'.$filename.':'.$linenum.'"');
			log_trace();
			break;
		default:
			addlog($erc.': '.$errmsg.' в "'.$filename.':'.$linenum.'"');
	}
	return true;
}

function log_trace(){
	$backtrace=debug_backtrace();
	$vars = $backtrace[1]['args'][4];
	unset($backtrace[0]);
	unset($backtrace[1]);
	$log_text = 'FUNCTION CALL BACKTRACE:';
	foreach($backtrace as $call)
		$log_text.="\r\n".$call['file'].':'.$call['line'].' - '.$call['function'].'('.trace_prep_args($call['args']).')';

	$log_text .= "\r\n".'VARIABLES DUMP = '.print_r($vars,1)."\r\n\r\n";
	addlog($log_text);
}

function trace_prep_args($args){
	$txt = '';
	$f=true;
	foreach($args as $v){
		if($f)
			$f=false;
		else
			$txt.=',';
		if(is_string($v))
			$txt.="'".$v."'";
		elseif(is_numeric($v))
			$txt.=$v;
		elseif(is_null($v))
			$txt.='NULL';
		elseif(is_bool($v))
			$txt.=$v?'true':'false';
		elseif(is_array($v) && count($v)==0)
			$txt.='array()';
		else
			$txt.=print_r($v,1);
	}
	return $txt;
}



?>