<form action="<?=$APPLICATION->GetCurPage()?>">
    <?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
	<input type="hidden" name="id" value="kolanches.task">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">
	<?= CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN")) ?>
	<p><?= GetMessage("MOD_UNINST_SAVE"); ?></p>
	<p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?= GetMessage("MOD_UNINST_SAVE_TABLES"); ?></label></p>
	<input type="submit" name="inst" value="<?= GetMessage("MOD_UNINST_DEL"); ?>">
</form>