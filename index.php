<?php
header('Content-Type: application/json');
echo json_encode([
  'status' => 'online',
  'app'    => 'sigespol-api',
  'time'   => date('c')
]);
