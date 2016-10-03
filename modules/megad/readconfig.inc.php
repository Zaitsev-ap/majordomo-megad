<?php

 $record=SQLSelectOne("SELECT * FROM megaddevices WHERE ID='".(int)$id."'");

 $url=BASE_URL.'/modules/megad/megad-cfg.php';
 $url.='?ip='.urlencode($record['IP']).'&read-conf='.urlencode(ROOT.'cached/megad.cfg').'&p='.urlencode($record['PASSWORD']);

 if ($this->config['API_IP']) {
  $url.='&local-ip='.$this->config['API_IP'];
 }

 $data=getURL($url, 0);

 if (preg_match('/OK/', $data)) {
  $record['CONFIG']=LoadFile(ROOT.'cached/megad.cfg');
  SQLUpdate('megaddevices', $record);
  //unlink(ROOT.'cached/megad.cfg');

  //process config
  if (preg_match_all('/pn=(\d+)&(.+?)'."\n".'/is', $record['CONFIG'], $m)) {
   $total=count($m[2]);

   $total++;

   for($i=0;$i<$total;$i++) {
    $port=$m[1][$i];
    $line=$m[2][$i];
    $type='';

    if (preg_match('/pty=(\d+)/', $line, $m2)) {
     $type=(int)$m2[1];
    } /*elseif (preg_match('/ecmd=/', $line)) {
     $type=0;       
    } else {
     $type=1;       
    }

    if ($port==14 || $port==15) {
     $type=2;
    }
    if ($i==16) {
     $port=16;
     $type=100;
    }*/
   

    if ($type!=='') {
     $prop=SQLSelectOne("SELECT * FROM megadproperties WHERE DEVICE_ID='".$record['ID']."' AND NUM='".$port."'");
     $prop['TYPE']=$type;
     $prop['NUM']=$port;
     $prop['DEVICE_ID']=$record['ID'];


     if (preg_match('/ecmd=(.*?)\&/', $line, $m3)) {
      $prop['ECMD']=$m3[1];
     }
     if (preg_match('/eth=(.*?)\&/', $line, $m3)) {
      $prop['ETH']=$m3[1];
     }
     if (preg_match('/m=(\d+)/', $line, $m3)) {
      $prop['MODE']=$m3[1];
     }
     if (preg_match('/d=(\d+)/', $line, $m3)) {
      $prop['DEF']=$m3[1];
     }
     if (preg_match('/misc=(.*?)\&/', $line, $m3)) {
      $prop['MISC']=$m3[1];
     }

     if (!$prop['ID']) {
      $prop['ID']=SQLInsert('megadproperties', $prop);
     } else {
      SQLUpdate('megadproperties', $prop);
     }
    }
   }


   $this->readValues($record['ID']);
  }

 }

