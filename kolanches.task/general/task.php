<?

class CKolanchesTask
{

    function GetByID($ID)
    {
        global $DB;
        $ID = intval($ID);

        $strSql = "
            SELECT
                T.*
            FROM kolanches_task T
            WHERE T.ID=".$ID."
        ";
        
        $dbResult = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
        if ( ($arResult = $dbResult->Fetch()) ){
            return $arResult;
        }
        
        return false;
    }

    function Add($arFields)
    {
        global $DB;
        
        $arInsert = $DB->PrepareInsert("kolanches_task", $arFields);

        $dbFields = ""; 
        $dbValues = "";
        foreach ($arFields as $key => $value)
        {
            if (strlen($dbFields) > 0) {
                $dbFields .= ", ";
            }
            $dbFields .= "`".$key."`";   
            
            if (strlen($dbValues) > 0) {
                $dbValues .= ", ";
            }
            $dbValues .= "'".$value."'";
        }
        
        if (strlen($dbFields) > 0)
        {
            $strSql =
                "INSERT INTO kolanches_task (".$dbFields.") ".
                "VALUES(".$dbValues.")";
            $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            
            $ID = IntVal($DB->LastID());
            return $ID;
        }
        return false;
    }

    function Delete($ID)
    {
        global $DB;
        $ID = intval($ID);
        $tSuccess = true;

        $DB->StartTransaction();

        if ($tSuccess) {
            $tSuccess = $DB->Query("DELETE FROM kolanches_task WHERE ID='".$ID."' ", false, 
                                   "File: ".__FILE__."<br>Line: ".__LINE__);
        }

        if($tSuccess) {
            $DB->Commit();
        } else {
            $DB->Rollback();
        }

        return $tSuccess;
    }

    function CheckFields($ACTION, &$arFields, $ID = 0)
    {
        global $APPLICATION, $DB;

        if ((is_set($arFields, "NAME") || $ACTION=="ADD") && strlen($arFields["NAME"]) <= 0)
        {
            $APPLICATION->ThrowException(GetMessage("KOL_TASK_EMPTY_NAME"), "EMPTY_NAME");
            return false;
        }

        if ((is_set($arFields, "DESCRIPTION") || $ACTION=="ADD") && strlen($arFields["DESCRIPTION"]) <= 0)
        {
            $APPLICATION->ThrowException(GetMessage("KOL_TASK_EMPTY_DESCRIPTION"), "EMPTY_DESCRIPTION");
            return false;
        }

        // готовность задачи
        if ((is_set($arFields, "COMPLETE") || $ACTION=="ADD") && 
                    $arFields[ "COMPLETE"] != "Y" && $arFields["COMPLETE"] != "N") 
        {
            $arFields["COMPLETE"]  = "Y";
        }

        return true;
    }
    
    function Update($ID, $arFields)
    {
        global $DB;
        $ID = intval($ID);
        
        if(!CKolanchesTask::CheckFields($arFields, $ID)) {
            return false;
        }
          
        $strUpdate = $DB->PrepareUpdate("kolanches_task", $arFields);
        
        if ($strUpdate != "") 
        {
            $strSql = "UPDATE kolanches_task SET ".$strUpdate." WHERE ID=".$ID;
            $arBinds = array(
                "NAME" => $arFields["NAME"],
                "DESCRIPTION" => $arFields["DESCRIPTION"],
                "COMPLETE" => $arFields["COMPLETE"],
            );
            
            if(!$DB->QueryBind($strSql, $arBinds)) {
                return false;
            }
        }

        return true;
    }
    
    function Complete($ID, $complete) {
        global $DB;
        $ID = intval($ID);
        
        $fields = Array(
          "COMPLETE"    => ($complete == "Y" ? "Y" : "N")
        );
        $strUpdate = $DB->PrepareUpdate("kolanches_task", $fields);
        
        if ($strUpdate != "") 
        {
            $strSql = "UPDATE kolanches_task SET ".$strUpdate." WHERE ID=".$ID;
            $arBinds = array(
                "COMPLETE" => $fields["COMPLETE"],
            );
            
            if(!$DB->QueryBind($strSql, $arBinds)) {
                return false;
            }
        }

        return true;
    }

    function GetList()
    {
        global $DB;
        
        $strSql = "SELECT * FROM kolanches_task"; 
        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }
    
    public static function GetListBy($arOrder = Array("ID" => "DESC"), $arFilter = array())
    {
        global $DB;
        $err_mess = "FILE: ".__FILE__."<br>LINE: ";

        $arSqlSearch = array();
        $arSqlOrder = array();

        if(!is_array($arFilter))
            $filter_keys = array();
        else
            $filter_keys = array_keys($arFilter);

        for($i = 0, $n = count($filter_keys); $i < $n; $i++)
        {
            $val = $arFilter[$filter_keys[$i]];
            $key = strtoupper($filter_keys[$i]);
            if(strlen($val)<=0 || ($key=="USER_ID" && $val!==false && $val!==null))
                continue;

            switch($key)
            {
                case "ID":
                    $arSqlSearch[] = "ID=".(int)$val;
                    break;
                case "~NAME":
                    $arSqlSearch[] = "NAME LIKE '".$DB->ForSQLLike($val)."'";
                    break;
                case "=NAME":
                    $arSqlSearch[] = "NAME = '".$DB->ForSQL($val)."'";
                    break;
                case "~DESCRIPTION":
                    $arSqlSearch[] = "DESCRIPTION LIKE '".$DB->ForSQLLike($val)."'";
                    break;
                case "=DESCRIPTION":
                    $arSqlSearch[] = "DESCRIPTION = '".$DB->ForSQL($val)."'";
                    break;
                case "COMPLETE":
                    $t_val = strtoupper($val);
                    if($t_val == "Y" || $t_val == "N")
                        $arSqlSearch[] = "COMPLETE='".$t_val."'";
                    break;
            }
        }

        foreach($arOrder as $by => $order)
        {
            $by = strtoupper($by);
            $order = strtoupper($order);

            if ($order != "ASC")
                $order = "DESC".($DB->type=="ORACLE" ? " NULLS LAST" : "");
            else
                $order = "ASC".($DB->type=="ORACLE" ? " NULLS FIRST" : "");
            $arSqlOrder[] = $by." ".$order;
        }

        $strSql = "SELECT ID, NAME, DESCRIPTION, COMPLETE ".
                  "FROM kolanches_task";
        $strSql .= (count($arSqlSearch)>0) ? " WHERE ".implode(" AND ", $arSqlSearch) : "";
        $strSql .= (count($arSqlOrder)>0) ? " ORDER BY ".implode(", ", $arSqlOrder) : "";

        $res = $DB->Query($strSql, false, $err_mess.__LINE__);

        return $res;
    }

}

