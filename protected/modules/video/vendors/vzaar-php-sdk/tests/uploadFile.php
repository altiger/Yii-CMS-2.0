<?php
/*
 * @author Skitsanos
*/
require_once '../Vzaar.php';

Vzaar::$token = '960DUk4qz9mFijtPwahllzpHYWQzKSVJiIyUpQ82Ac'; //
Vzaar::$secret = 'skitsanos';

$filename = 'video.flv'; // the file must be located in the same directory as the script. If not use full disk path
$file=getcwd().'\\'.$filename;

$result=Vzaar::uploadVideo($file);
//$result = Vzaar::processVideo('vzf114a1d04d314fb9b4122f85998e0698', 'broken video', '');

print_r($result);

?>