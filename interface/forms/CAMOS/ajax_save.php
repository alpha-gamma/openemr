<?php
//------------This file inserts your field data into the MySQL database
require_once("../../globals.php");
require_once("../../../library/api.inc");
require_once("../../../library/forms.inc");
require_once("content_parser.php");

if (!verifyCsrfToken($_POST["csrf_token_form"])) {
    csrfNotVerified();
}

$field_names = array('category' => $_POST["category"], 'subcategory' => $_POST["subcategory"], 'item' => $_POST["item"], 'content' => $_POST['content']);
$camos_array = array();
process_commands($field_names['content'], $camos_array);

$CAMOS_form_name = "CAMOS-".$field_names['category'].'-'.$field_names['subcategory'].'-'.$field_names['item'];

if ($encounter == "") {
    $encounter = date("Ymd");
}

if (preg_match("/^[\s\\r\\n\\\\r\\\\n]*$/", $field_names['content']) == 0) { //make sure blanks do not get submitted
  // Replace the placeholders before saving the form. This was changed in version 4.0. Previous to this, placeholders
  //   were submitted into the database and converted when viewing. All new notes will now have placeholders converted
  //   before being submitted to the database. Will also continue to support placeholder conversion on report
  //   views to support notes within database that still contain placeholders (ie. notes that were created previous to
  //   version 4.0).
    $field_names['content'] = replace($pid, $encounter, $field_names['content']);
    reset($field_names);
    $newid = formSubmit("form_CAMOS", $field_names, $_GET["id"], $userauthorized);
    addForm($encounter, $CAMOS_form_name, $newid, "CAMOS", $pid, $userauthorized);
}

//deal with embedded camos submissions here
foreach ($camos_array as $val) {
    if (preg_match("/^[\s\\r\\n\\\\r\\\\n]*$/", $val['content']) == 0) { //make sure blanks not submitted
        foreach ($val as $k => $v) {
            // Replace the placeholders before saving the form. This was changed in version 4.0. Previous to this, placeholders
            //   were submitted into the database and converted when viewing. All new notes will now have placeholders converted
            //   before being submitted to the database. Will also continue to support placeholder conversion on report
            //   views to support notes within database that still contain placeholders (ie. notes that were created previous to
            //   version 4.0).
            $val[$k] = trim(replace($pid, $encounter, $v));
        }

        $CAMOS_form_name = "CAMOS-".$val['category'].'-'.$val['subcategory'].'-'.$val['item'];
        reset($val);
        $newid = formSubmit("form_CAMOS", $val, $_GET["id"], $userauthorized);
        addForm($encounter, $CAMOS_form_name, $newid, "CAMOS", $pid, $userauthorized);
    }
}

echo "<font color=red><b>" . xlt('submitted') . ": " . text(time()) . "</b></font>";
