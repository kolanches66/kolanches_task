<?
global $MESS;

$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));



Class kolanches_task extends CModule
{
	var $MODULE_ID = "kolanches.task";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function kolanches_task()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
        
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("KOL_TASK_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("KOL_TASK_INSTALL_DESCRIPTION");
        
		$this->PARTNER_NAME = GetMessage("KOL_TASK_PARTNER");
		$this->PARTNER_URI = GetMessage("PARTNER_URI");
	}

    
    
  function DoInstall() {
    global $APPLICATION, $step;

    $this->InstallDB(false);
    $this->InstallFiles();

    $APPLICATION->IncludeAdminFile(
        GetMessage("KOL_TASK_INSTALL_TITLE"), 
        $_SERVER["DOCUMENT_ROOT"] .
            "/bitrix/modules/kolanches.task/install/step.php"
    );
	}
    
  function InstallFiles()  {
		if($_ENV["COMPUTERNAME"]!='BX') {
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . 
              "/bitrix/modules/kolanches.task/install/admin", 
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true
        );
		}
		return true;
	}

	function InstallDB($install_wizard = true)
	{
		global $DB, $DBType, $APPLICATION;

		if (!$DB->Query("SELECT 'x' FROM kolanches_task", true))
		{
			$errors = $DB->RunSQLBatch(
                $_SERVER["DOCUMENT_ROOT"] . 
                    "/bitrix/modules/kolanches.task/install/" . 
                    $DBType."/install.sql"
            );
        }
        
        if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		RegisterModule("kolanches.task");

		return true;
	}
    
    
    
  function DoUninstall() {
    global $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = IntVal($step);
        
		if($step < 2) {
        $APPLICATION->IncludeAdminFile(
            GetMessage("KOL_TASK_UNINSTALL_TITLE"), 
            $DOCUMENT_ROOT."/bitrix/modules/kolanches.task/install/unstep1.php"
        );  
		} 
    elseif ($step == 2) 
    {
      $this->UnInstallDB(array(
        "savedata" => $_REQUEST["savedata"],
      ));
      
      $this->UnInstallFiles();

      $APPLICATION->IncludeAdminFile(
          GetMessage("KOL_TASK_UNINSTALL_TITLE"), 
          $DOCUMENT_ROOT."/bitrix/modules/kolanches.task/install/unstep2.php"
      );
		}
	}
    
  function UnInstallFiles($arParams = array()) {
		DeleteDirFiles(
      $_SERVER["DOCUMENT_ROOT"] . 
        "/bitrix/modules/kolanches.task/install/admin/", 
      $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
    );

		return true;
	}

	function UnInstallDB($arParams = Array()) {
		global $APPLICATION, $DB, $errors;
		$this->errors = false;

		if (!$arParams['savedata'])
		{
			$this->errors = $DB->RunSQLBatch(
          $_SERVER['DOCUMENT_ROOT'] . 
          "/bitrix/modules/kolanches.task/install/" . 
          strtolower($DB->type) . "/uninstall.sql"
      );
		}

		if(!empty($this->errors))
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}
        
    UnRegisterModule("kolanches.task");
	}  
    
}
