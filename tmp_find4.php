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
$found=false;
foreach($variants as $var){
  $s1 = $base; $s2 = $base; $s3 = $var['row2']; $s4 = $var['row2'];
  $b1=$geo->boundingBox($s1); $b3=$geo->boundingBox($s3);
  for($dx1=180; $dx1<=500; $dx1+=1){
    for($y2=180; $y2<=340; $y2+=1){
      for($sx=0; $sx<=200; $sx+=1){
        $p1=$geo->translate($s1,0,0);
        $p2=$geo->translate($s2,$dx1,0);
        $p3=$geo->translate($s3,$sx,$y2);
        $p4=$geo->translate($s4,$sx+$dx1,$y2);
        $arr=[$p1,$p2,$p3,$p4];
        $ok=true;
        foreach($arr as $p){$b=$geo->boundingBox($p); if($b['max_x']>$sheetW||$b['max_y']>$sheetH){$ok=false; break;}}
        if(!$ok) continue;
        for($i=0;$i<4;$i++){for($j=$i+1;$j<4;$j++){if($geo->intersects($arr[$i],$arr[$j])){$ok=false; break 2;}}}
        if($ok){echo "FOUND variant={$var['name']} dx=$dx1 y2=$y2 sx=$sx\n"; $found=true; exit;}
      }
    }
  }
}
if(!$found) echo "NONE\n";
