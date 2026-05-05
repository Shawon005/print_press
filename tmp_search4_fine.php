<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$geo = app(App\Services\DieGeometryService::class);
$shape = $geo->generateCartonPolygon([
  'body_width_mm' => 5 * 25.4,
  'body_height_mm' => 8 * 25.4,
  'top_flap_mm' => 3 * 25.4,
  'bottom_flap_mm' => 3 * 25.4,
  'side_flap_mm' => 2 * 25.4,
  'glue_flap_mm' => 1 * 25.4,
  'bleed_mm' => 0,
]);
$sheetW=36*25.4; $sheetH=26*25.4;
$bbox=$geo->boundingBox($shape); $w=$bbox['width']; $h=$bbox['height'];
$found=false;
for($xStep=260; $xStep<=450; $xStep+=1){
  if($xStep + $w > $sheetW) break;
  for($yStep=203; $yStep<=299; $yStep+=1){ // around 8in to max
    if($yStep + $h > $sheetH) break;
    for($st=0; $st<=$xStep; $st+=1){
      $all=[
        $geo->translate($shape,0,0),
        $geo->translate($shape,$xStep,0),
        $geo->translate($shape,$st,$yStep),
        $geo->translate($shape,$st+$xStep,$yStep),
      ];
      $ok=true;
      foreach($all as $p){$b=$geo->boundingBox($p); if($b['max_x']>$sheetW||$b['max_y']>$sheetH){$ok=false; break;}}
      if(!$ok) continue;
      for($i=0;$i<4;$i++){
        for($j=$i+1;$j<4;$j++){
          if($geo->intersects($all[$i],$all[$j])){$ok=false; break 2;}
        }
      }
      if($ok){echo "FOUND xStep=$xStep yStep=$yStep st=$st\n"; $found=true; break 3;}
    }
  }
}
if(!$found) echo "NONE\n";
