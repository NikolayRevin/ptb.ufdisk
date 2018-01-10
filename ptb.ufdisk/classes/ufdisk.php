<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Disk\Internals\StorageTable;

Loc::loadLanguageFile(__FILE__);

class CPtbUserTypeDisk
{
    public static function GetUserTypeDescription()
    {
        return [
            "USER_TYPE_ID" => "ptbdisc",
            "CLASS_NAME" => "CPtbUserTypeDisk",
            "DESCRIPTION" => Loc::getMessage("USER_TYPE_DISK_NAME") . '1231232',
            "BASE_TYPE" => "int"
        ];
    }
    
    public static function GetDBColumnType()
    {
        global $DB;
        switch (strtolower($DB->type)) {
            case "mysql":
                return "int(18)";
            case "oracle":
                return "number(18)";
            case "mssql":
                return "int";
        }
    }
    
    public static function PrepareSettings($arUserField)
    {
        $entityTypes = $arUserField["SETTINGS"]["ENTITY_TYPE"];
        
        if (! is_array($entityTypes))
            $entityTypes = [];
            
            return [
                'ENTITY_TYPE' => (in_array("NOT_REF", $entityTypes) ? [] : $entityTypes)
            ];
    }
    
    private static function GetEntityTypeArray()
    {
        $result = [];
        
        if (Loader::includeModule('disk')) {
            $rs = StorageTable::getList([
                'group' => [
                    'ENTITY_TYPE'
                ],
                'select' => [
                    'ENTITY_TYPE'
                ]
            ]);
            
            while ($ar = $rs->fetch()) {
                $result[$ar['ENTITY_TYPE']] = $ar['ENTITY_TYPE'];
            }
        }
        
        return $result;
    }
    
    private static function GetStorageByID(int $ID)
    {
        $ID = intval($ID);
        static $CACHE = array();
        
        if (! is_set($CACHE, $ID) && CModule::IncludeModule('disk')) {
            $CACHE[$ID] = StorageTable::getRow([
                'filter' => [
                    "ID" => $ID
                ]
            ]);
        }
        return $CACHE[$ID];
    }
    
    private static function GetReferenceArray(array $arr)
    {
        $res = array(
            "REFERENCE" => array(),
            "REFERENCE_ID" => array()
        );
        foreach ($arr as $key => $val) {
            $res["REFERENCE"][] = $val;
            $res["REFERENCE_ID"][] = $key;
        }
        return $res;
    }
    
    private static function GetStorageList(array $arFilter)
    {
        $result = [];
        
        if (Loader::includeModule('disk')) {
            $rs = StorageTable::getList([
                'filter' => $arFilter,
                'order' => [
                    'ENTITY_TYPE' => 'ASC',
                    'NAME' => 'ASC'
                ]
            ]);
            
            while ($ar = $rs->fetch()) {
                if (! is_array($result[$ar['ENTITY_TYPE']])) {
                    $result[$ar['ENTITY_TYPE']] = [];
                }
                $result[$ar['ENTITY_TYPE']][$ar['ID']] = $ar;
            }
        }
        
        return $result;
    }
    
    public static function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
    {
        $result = '';
        $value = [];
        if ($bVarsFromForm)
            $value = $GLOBALS[$arHtmlControl["NAME"]]["ENTITY_TYPE"];
            elseif (is_array($arUserField))
            $value = $arUserField["SETTINGS"]["ENTITY_TYPE"];
            
            $result .= '
		<tr valign="top">
			<td>' . Loc::getMessage("USER_TYPE_DISK_ENTITY_TYPE") . ':</td>
			<td>' . SelectBoxMFromArray($arHtmlControl["NAME"] . '[ENTITY_TYPE][]', self::GetReferenceArray(self::GetEntityTypeArray()), $value, Loc::getMessage("MAIN_ALL")) . '</td>
		</tr>';
            
            return $result;
    }
    
    public static function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        $arStorageList = self::GetStorageList([
            '=ENTITY_TYPE' => $arUserField["SETTINGS"]["ENTITY_TYPE"]
        ]);
        
        if ($arUserField["MULTIPLE"] != "Y") {
            $arHtmlControl["VALUE"] = array(
                $arHtmlControl["VALUE"]
            );
        }
        
        ob_start();
        
        $ID = md5($arHtmlControl["NAME"]);
        
        ?><select name="<? echo $arHtmlControl["NAME"] ?>"
	<?=$arUserField["MULTIPLE"] == "Y" ? ' multiple size="5"' : '' ?>
	id="admin_select_<?=$ID?>">
	<option value=""><? ?></option>
        		<?foreach ($arStorageList as $type => $items): ?>
        			<optgroup label="<?=$type ?>"><?=$type ?></optgroup>
        			<?foreach ($items as $item): ?>
        			<option value="<?=$item['ID'] ?>"
		<?=in_array($item["ID"], $arHtmlControl["VALUE"]) ? ' selected' : '' ?>><?=$item['NAME'] ?> [<?=$item['ID'] ?>]</option>
        			<?endforeach; ?>
        		<?endforeach; ?>
        </select><?
        
        $html .= ob_get_contents();
        ob_end_clean();
        
        return $html;
    }

    public static function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
    {
        return self::GetEditFormHTML($arUserField, $arHtmlControl);
    }

    public static function GetAdminListViewHTML($arUserField, $arHtmlControl)
    {
        if ((strlen($arHtmlControl["VALUE"]) > 0) && ($ar = self::GetStorageByID($arHtmlControl["VALUE"])))
            return $ar["NAME"];
        else
            return '&nbsp;';
    }

    public static function OnBeforeSave($arUserField, $value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val)
                if (! intval($val))
                    unset($value[$key]);
        } elseif (strlen($value) > 0) {
            $value = intval($value);
            if (! $value)
                $value = "";
        }
        return $value;
    }
}

?>