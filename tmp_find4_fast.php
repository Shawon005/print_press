<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$geo = app(App\Services\DieGeometryService::class);
$base = $geo->generateCartonPolygon([
  'body_width_mm' => 5 * 25.4,
  'body_height_mm' => 8 * 25.4,
  'top_flap_mm' => 3 * 25.4,
  'bottom_flap_mm' => 3 * 25.4,
  'side_flap_mm' => 2 * 25.4,
  'glue_flap_mm' => 1 * 25.4,
  'bleed_mm' => 0.12 * 25.4,
]);
$sheetW = 36*25.4; $sheetH = 26*25.4;
$variants = [
  ['name'=>'same', 'row2'=>$base],
  ['name'=>'mirror', 'row2'=>$geo->mirrorX($base)],
  ['name'=>'rot180', 'row2'=>$geo->rotate($base,180)],
  ['name'=>'rot180_mirror', 'row2'=>$geo->mirrorX($geo->rotate($base,180))],
];
foreach($variants as $var){
  for($dx1=220; $dx1<=420; $dx1+=2){
    for($y2=200; $y2<=320; $y2+=2){
      for($sx=0; $sx<=180; $sx+=2){
        $arr=[
          $geo->translate($base,0,0),
          $geo->translate($base,$dx1,0),
          $geo->translate($var['row2'],$sx,$y2),
          $geo->translate($var['row2'],$sx+$dx1,$y2),
        ];
        $ok=true;
        foreach($arr as $p){$b=$geo->boundingBox($p); if($b['max_x']>$sheetW||$b['max_y']>$sheetH){$ok=false; break;}}
        if(!$ok) continue;
        for($i=0;$i<4;$i++){for($j=$i+1;$j<4;$j++){if($geo->intersects($arr[$i],$arr[$j])){$ok=false; break 2;}}}
        if($ok){echo "FOUND variant={$var['name']} dx=$dx1 y2=$y2 sx=$sx\n"; exit;}
      }
    }
  }
}
echo "NONE\n";
