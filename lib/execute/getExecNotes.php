<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: getExecNotes.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2009/06/03 21:16:17 $ by $Author: franciscom $
 *
 *
 * 20090530: franciscom - try to improve usability in order to allow edit online
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once("web_editor.php");
require_once('exec.inc.php');

$webeditorCfg = getWebEditorCfg('execution');
require_once(require_web_editor($webEditorCfg['type']));


testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tcase_mgr = new testcase($db);
$args = init_args();

$webeditorCfg = getWebEditorCfg('execution');
$map = get_execution($db,$args->exec_id);

$notes_editor=createExecNotesWebEditor($args->exec_id,$_SESSION['basehref'],$webeditorCfg,$map[0]['notes']);



$smarty = new TLSmarty();
$smarty->assign('notes',$map[0]['notes']);
$smarty->assign('notes',$notes_editor);

$smarty->assign('webeditorType',$webeditorCfg['type']);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



function createExecNotesWebEditor($id,$basehref,$editorCfg,$content=null)
{
    // Important Notice:
    //
    // When using tinymce or none as web editor, we need to set rows and cols
    // to appropriate values, to avoid an ugly ui.
    // null => use default values defined on editor class file
    //
    // Rows and Cols values are useless for FCKeditor.
    //
    $of=web_editor("exec_notes_$id",$basehref,$editorCfg) ;
    $of->Value = $content;
    $editor=$of->CreateHTML(10,60);         
    unset($of);
    return $editor;
}



function init_args()
{
    $iParams = array("exec_id" => array(tlInputParameter::INT_N));
	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
    
    return $args; 
}
?>