<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
include_once 'vtlib/Vtiger/Utils.php';

if (isset($_REQUEST['module_settings']) && $_REQUEST['module_settings'] == 'true') {
	$targetmodule = vtlib_purify($_REQUEST['formodule']);

	$targetSettingPage = "modules/$targetmodule/Settings.php";
	if (file_exists($targetSettingPage)) {
		Vtiger_Utils::checkFileAccessForInclusion($targetSettingPage);
		require_once $targetSettingPage;
	}
} else {
	$modulemanager_uploaddir = 'cache/vtlib';
	if (!is_dir($modulemanager_uploaddir)) {
		@unlink($modulemanager_uploaddir);
		mkdir($modulemanager_uploaddir);
	}
	if (!empty($_REQUEST['module_import'])) {
		require_once 'modules/Settings/ModuleManager/Import.php';
		exit;
	} elseif (!empty($_REQUEST['module_update'])) {
		require_once 'modules/Settings/ModuleManager/Update.php';
		exit;
	} elseif (isset($_REQUEST['module_import_cancel']) && $_REQUEST['module_import_cancel'] == 'true') {
		$uploadfile = basename(vtlib_purify($_REQUEST['module_import_file']));
		$uploadfilename = "$modulemanager_uploaddir/$uploadfile";
		checkFileAccess($uploadfilename);
		if (file_exists($uploadfilename)) {
			unlink($uploadfilename);
		}
	}

	require_once 'Smarty_setup.php';

	global $mod_strings,$app_strings,$theme;
	$smarty = new vtigerCRM_Smarty;
	$smarty->assign('MOD', $mod_strings);
	$smarty->assign('APP', $app_strings);
	$smarty->assign('MODULE', $currentModule);
	$smarty->assign('SINGLE_MOD', getTranslatedString('SINGLE_'.$currentModule));
	$smarty->assign('THEME', $theme);
	$smarty->assign('IMAGE_PATH', "themes/$theme/images/");

	$module_disable = isset($_REQUEST['module_disable']) ? vtlib_purify($_REQUEST['module_disable']) : '';
	$module_name = isset($_REQUEST['module_name']) ? vtlib_purify($_REQUEST['module_name']) : '';
	$module_enable = isset($_REQUEST['module_enable']) ? vtlib_purify($_REQUEST['module_enable']) : '';
	$module_type = isset($_REQUEST['module_type']) ? vtlib_purify($_REQUEST['module_type']) : '';

	if ($module_name != '') {
		if ($module_type == 'language') {
			if ($module_enable == 'true') {
				vtlib_toggleLanguageAccess($module_name, true);
			}
			if ($module_disable== 'true') {
				vtlib_toggleLanguageAccess($module_name, false);
			}
		} else {
			if ($module_enable == 'true') {
				vtlib_toggleModuleAccess($module_name, true);
			}
			if ($module_disable== 'true') {
				vtlib_toggleModuleAccess($module_name, false);
			}
		}
	}

	// Check write permissions on the required directories
	$dir_notwritable = array();
	if (!vtlib_isDirWriteable('cache/vtlib')) {
		$dir_notwritable[] = 'cache/vtlib';
	}
	if (!vtlib_isDirWriteable('cron/modules')) {
		$dir_notwritable[] = 'cron/modules';
	}
	if (!vtlib_isDirWriteable('modules')) {
		$dir_notwritable[] = 'modules';
	}
	if (!vtlib_isDirWriteable('Smarty/templates/modules')) {
		$dir_notwritable[] = 'Smarty/templates/modules';
	}

	$smarty->assign('DIR_NOTWRITABLE_LIST', $dir_notwritable);
	// END

	$smarty->assign('TOGGLE_MODINFO', vtlib_getToggleModuleInfo());
	$smarty->assign('TOGGLE_LANGINFO', vtlib_getToggleLanguageInfo());
	$smarty->assign('coreBOSOnDemandActive', $coreBOSOnDemandActive);

	$mode = !empty($_REQUEST['mode']) ? vtlib_purify($_REQUEST['mode']) : '';
	$smarty->assign('MODE', $mode);

	if (empty($_REQUEST['ajax']) || $_REQUEST['ajax'] != 'true') {
		$smarty->display('Settings/ModuleManager/ModuleManager.tpl');
	} else {
		$smarty->display('Settings/ModuleManager/ModuleManagerAjax.tpl');
	}
}
?>