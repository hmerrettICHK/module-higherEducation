<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/institutions_manage_edit.php') == false) {

    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Institutions'), 'institutions_manage.php');
    $page->breadcrumbs->add(__('Edit Institution'));

    $role = staffHigherEducationRole($_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role != 'Coordinator') {
        $page->addError(__('You do not have access to this action.'));
    } else {
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if school year specified
        $higherEducationInstitutionID = $_GET['higherEducationInstitutionID'];
        if ($higherEducationInstitutionID == 'Y') {
            $page->addError(__('You have not specified an activity.'));
        } else {
            try {
                $data = array('higherEducationInstitutionID' => $higherEducationInstitutionID);
                $sql = 'SELECT * FROM higherEducationInstitution WHERE higherEducationInstitutionID=:higherEducationInstitutionID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $page->addError(__('The student cannot be edited due to a database error.'));
            }

            if ($result->rowCount() != 1) {
                $page->addError(__('The selected activity does not exist.'));
            } else {
                //Let's go!
                $row = $result->fetch();
                ?>
                <form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/institutions_manage_editProcess.php?higherEducationInstitutionID=$higherEducationInstitutionID" ?>">
                    <table class='smallIntBorder' cellspacing='0' style="width: 100%">
                        <tr>
                            <td>
                                <b>Name *</b><br/>
                                <span style="font-size: 90%"><i></i></span>
                            </td>
                            <td class="right">
                                <input name="name" id="uniname" maxlength=150 value="<?php echo $row['name'] ?>" type="text" style="width: 300px">
                                <script type="text/javascript">
                                    var uniname=new LiveValidation('uniname');
                                    uniname.add(Validate.Presence);
                                 </script>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Country *</b><br/>
                                <span style="font-size: 90%"><i></i></span>
                            </td>
                            <td class="right">
                                <select name="country" id="country" style="width: 302px">
                                    <?php
                                    echo "<option value='Please select...'>Please select...</option>";
                                    try {
                                        $dataSelect = array();
                                        $sqlSelect = 'SELECT printable_name FROM gibbonCountry ORDER BY printable_name';
                                        $resultSelect = $connection2->prepare($sqlSelect);
                                        $resultSelect->execute($dataSelect);
                                    } catch (PDOException $e) {
                                    }
                                    while ($rowSelect = $resultSelect->fetch()) {
                                        $selected = '';
                                        if ($row['country'] == $rowSelect['printable_name']) {
                                            $selected = 'selected';
                                        }
                                        echo "<option $selected value='".$rowSelect['printable_name']."'>".htmlPrep($rowSelect['printable_name']).'</option>';
                                    }
                                    ?>
                                </select>
                                <script type="text/javascript">
                                    var country=new LiveValidation('country');
                                    country.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
                                 </script>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Active *</b><br/>
                            </td>
                            <td class="right">
                                <select name="active" id="active" style="width: 302px">
                                    <option <?php if ($row['active'] == 'Y') { echo ' selected '; } ?>value="Y">Y</option>
                                    <option <?php if ($row['active'] == 'N') { echo ' selected '; } ?>value="N">N</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span style="font-size: 90%"><i>* denotes a required field</i></span>
                            </td>
                            <td class="right">
                                <input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
                                <input type="submit" value="Submit">
                            </td>
                        </tr>
                    </table>
                </form>
                <?php

            }
        }
    }
}
?>
