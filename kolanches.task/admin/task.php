<?

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kolanches.task/include.php");

IncludeModuleLangFile(__FILE__);



// Создаем таблицу значений из БД

$sTableID = "tbl_kolanches_task";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);



// Поля, используемые в фильтре

$arFilterFields = array(
  "filter_id",
	"filter_name",
	"filter_complete",
);
$USER_FIELD_MANAGER->AdminListAddFilterFields("KOLANCHES_TASK", $arFilterFields);




// Создаем фильтр, получаем значения из формы

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
if (strlen($filter_id) > 0)
	$arFilter["ID"] = $filter_id;
if (strlen($filter_name) > 0)
	$arFilter["~NAME"] = "%".$filter_name."%";
if (strlen($filter_complete) > 0)
	$arFilter["COMPLETE"] = $filter_complete;

$USER_FIELD_MANAGER->AdminListAddFilter("KOLANCHES_TASK", $arFilter);



// Групповые действия (чекбоксы)

if (($arID = $lAdmin->GroupAction())) //&& $blogModulePermissions >= "W")
{
    // Получаем строки из БД по выбранным чекбоксам
  
	if ($_REQUEST['action_target'] == 'selected')
	{
		$arID = Array();
		$dbResultList = CKolanchesTask::GetListBy(
            array($by => $order),
            $arFilter
        );
        
		while ($arResult = $dbResultList->Fetch()) {
			$arID[] = $arResult['ID'];
    }
	}
    
    // Работаем с этими строками
    
    foreach ($arID as $ID)
	{
        // Невалидный ID игнорируем
		if (strlen($ID) <= 0)
			continue;
        
		switch ($_REQUEST['action'])
		{
            // Отмечаем 'Выполнено' \ 'Не выполнено'
            case "complete":
            case "uncomplete":
                @set_time_limit(0);
                $dbTask = CKolanchesTask::GetListBy(
                    array($by => $order),
                    $arFilter
                );
                $dbTaskOld = $dbTask->Fetch();

                $DB->StartTransaction();
              
                $complete = ($_REQUEST['action'] == "complete" ? "Y" : "N");
                
                if (!CKolanchesTask::Complete($ID, $complete)) {
                    $DB->Rollback();

                    if ($ex = $APPLICATION->GetException())
                        $lAdmin->AddGroupError($ex->GetString(), $ID);
                    else
                        $lAdmin->AddGroupError(GetMessage("KOL_TASK_DELETE_ERROR"), $ID);
                }
                $DB->Commit();
                break;
                
            // Удаляем задачи
			case "delete":
                @set_time_limit(0);
				$dbTask = CKolanchesTask::GetListBy(
                    array($by => $order),
                    $arFilter
                );
				$dbTaskOld = $dbTask->Fetch();

				$DB->StartTransaction();

				if (!CKolanchesTask::Delete($ID)) {
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("KOL_TASK_DELETE_ERROR"), $ID);
				}
				$DB->Commit();
				break;
		}
	}
}

// Заголовки таблицы, сортировка

$arHeaders = array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("KOL_TASK_NAME"), "sort"=>"NAME", "default"=>true),
    array("id"=>"DESCRIPTION", "content"=>GetMessage("KOL_TASK_DESCRIPTION"), "sort"=>"DESCRIPTION", "default"=>true),
	array("id"=>"COMPLETE", "content"=>GetMessage('KOL_TASK_COMPLETE'), "sort"=>"COMPLETE", "default"=>true),
);
$USER_FIELD_MANAGER->AdminListAddHeaders("KOLANCHES_TASK", $arHeaders);
$lAdmin->AddHeaders($arHeaders);

$dbResultList = CKolanchesTask::GetListBy(
	array($by => $order),
	$arFilter
);



// Пагинация и кол-во задач на странице

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("KOL_TASK_GROUP_NAV")));



while ($arTasks = $dbResultList->NavNext(true, "f_"))
{      
    // Форматирование значений в удобный вид
  
	$row =& $lAdmin->AddRow($f_ID, $arTasks, "/bitrix/admin/kolanches_task_edit.php?ID=".$f_ID."&lang=".LANGUAGE_ID, GetMessage("KOL_TASK_UPDATE_ALT"));
	$row->AddField("ID", '<a href="/bitrix/admin/kolanches_task_edit.php?ID='.$f_ID.'&lang='.LANGUAGE_ID.'" title="'.GetMessage("KOL_TASK_UPDATE_ALT").'">'.$f_ID.'</a>');
	$row->AddField("NAME", $f_NAME);
    $row->AddField("DESCRIPTION", $f_DESCRIPTION);
    $row->AddField("COMPLETE", (($f_COMPLETE == "Y") ? 
      "<b><span style='color:green'>" . GetMessage("KOL_TASK_YES") . "</span></b>": 
      "<span style='color:red'>"  .  GetMessage("KOL_TASK_NO") . "</span>"
    ));
    
    // Контексное меню при клике на гамбургер
	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("KOL_TASK_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("kolanches_task_edit.php?ID=".$f_ID."&lang=".LANG."&".GetFilterParams("filter_").""), "DEFAULT"=>true);
	//if ($blogModulePermissions >= "U") {
    $arActions[] = array("SEPARATOR" => true);
    $arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("KOL_TASK_DELETE_ALT"), "ACTION"=>"if(confirm('".GetMessage('KOL_TASK_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	//}
    $row->AddActions($arActions);
}

// Действия для групповой обработки

$lAdmin->AddGroupActionTable(
	array(
        "complete" => "Выполнено",
        "uncomplete" => "Не выполнено",
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
	)
);



//$lAdmin->AddFooter(
//	array(
//		array(
//			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
//			"value" => $dbResultList->SelectedRowsCount()
//		),
//		array(
//			"counter" => true,
//			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
//			"value" => "0"
//		),
//	)
//);



// Кнопка 'Новая задача'

$aContext = array(
    array(
        "TEXT" => GetMessage("KOL_TASK_ADD_NEW"),
        "ICON" => "btn_new",
        "LINK" => "kolanches_task_edit.php?lang=".LANG,
        "TITLE" => GetMessage("KOL_TASK_ADD_NEW_ALT")
    ),
);
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();



// Форма для фильтра

$APPLICATION->SetTitle(GetMessage("KOL_TASK_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("KOL_TASK_FILTER_COMPLETE"),
	)
);

$oFilter->Begin();
?>
    <tr>
		<td><?echo GetMessage("KOL_TASK_FILTER_ID")?>:</td>
		<td><input type="text" name="filter_id" value="<?echo htmlspecialcharsbx($filter_id)?>" size="40"><?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("KOL_TASK_FILTER_NAME")?>:</td>
		<td><input type="text" name="filter_name" value="<?echo htmlspecialcharsbx($filter_name)?>" size="40"><?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("KOL_TASK_FILTER_COMPLETE")?>:</td>
		<td>
			<select name="filter_complete">
				<option value=""><?echo GetMessage("KOL_TASK_F_ALL")?></option>
				<option value="Y"<?if ($filter_complete=="Y") echo " selected"?>><?echo GetMessage("KOL_TASK_YES")?></option>
				<option value="N"<?if ($filter_complete=="N") echo " selected"?>><?echo GetMessage("KOL_TASK_NO")?></option>
			</select>
		</td>
	</tr>
<?
$USER_FIELD_MANAGER->AdminListShowFilter("KOLANCHES_TASK");

$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>

<?
$lAdmin->DisplayList();
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
