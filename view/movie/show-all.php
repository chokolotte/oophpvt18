<?php

namespace Anax\View;

/**
* Template file to render a view
*/

// show incoming variables and view helper functions
//echo showEnvironment(get_defined_vars(), get_defined_functions());

if (!$res) {
    return;
}
?>
<table>
    <tr class="first">
        <th>Rad</th>
        <th>Id</th>
        <th>Bild</th>
        <th>Titel</th>
        <th>År</th>
    </tr>
<?php $id = -1; foreach ($res as $row) :
    $id++;
?>
    <tr>
        <td><?= $id ?></td>
        <td><?= $row->id ?></td>
        <td><img class="thumb" src="<?= asset($row->image) ?>"></td>
        <td><?= $row->title ?></td>
        <td><?= $row->year ?></td>
    </tr>
<?php endforeach; ?>
</table>
<?php require("footer.php");
