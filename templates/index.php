<?php

$url = \OC::$server->getURLGenerator()->linkToRoute('paheko.page.index') . 'app/';

?>
<iframe id="rliframe" style="border: none; width: 100%; min-height: 100%; position: relative; background: transparent" tabindex="-1" frameborder="0" src="<?=$url?>"></iframe>
