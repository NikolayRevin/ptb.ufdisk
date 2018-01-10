<?
IncludeModuleLangFile(__FILE__);

class ptb_ufdisk extends CModule
{

    const MODULE_ID = 'ptb.ufdisk';

    var $MODULE_ID = 'ptb.ufdisk';

    var $MODULE_VERSION;

    var $MODULE_VERSION_DATE;

    var $MODULE_NAME;

    var $MODULE_DESCRIPTION;

    var $MODULE_CSS;

    function __construct()
    {
        $arModuleVersion = array();
        include (dirname(__FILE__) . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("ptb.ufdisk_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("ptb.ufdisk_MODULE_DESC");
        
        $this->PARTNER_NAME = GetMessage("ptb.ufdisk_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("ptb.ufdisk_PARTNER_URI");
    }

    function InstallDB()
    {
        return true;
    }

    function UnInstallDB()
    {
        return true;
    }

    function InstallEvents()
    {
        RegisterModule(self::MODULE_ID);
        RegisterModuleDependences('main', 'OnUserTypeBuildList', self::MODULE_ID, 'CPtbUserTypeDisk', 'GetUserTypeDescription');
        
        return true;
    }

    function UnInstallEvents()
    {
        UnRegisterModuleDependences('main', 'OnUserTypeBuildList', self::MODULE_ID, 'CPtbUserTypeDisk', 'GetUserTypeDescription');
        UnRegisterModule(self::MODULE_ID);
        
        return true;
    }

    function InstallFiles()
    {
        return true;
    }

    function UnInstallFiles()
    {
        return true;
    }

    function DoInstall()
    {
        global $APPLICATION, $step;
        
        $step = IntVal($step);
        
        if ($step < 2) {
            
            if (! IsModuleInstalled('disk')) {
                $APPLICATION->ThrowException(GetMessage('ptb.ufdisk_MODULE_INSTALL_ERROR'));
            }
            
            $APPLICATION->IncludeAdminFile(GetMessage("ptb.ufdisk_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/ptb.ufdisk/install/step1.php");
        } else {
            if (! check_bitrix_sessid()) {
                $step = 1;
            }
            
            if ($step == 2) {
                $this->InstallEvents();
                $this->InstallFiles();
                $this->InstallDB();
            }
        }
    }

    function DoUninstall()
    {
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        $this->UnInstallDB();
    }
}
?>