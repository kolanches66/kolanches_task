<?

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::AddAutoloadClasses(
	"kolanches.task",
	array(
    "CKolanchesTask" =>   "general/task.php",
	)
);
