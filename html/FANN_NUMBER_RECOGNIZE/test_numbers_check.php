<?php

declare(strict_types=1);

$response = null;

$data = json_decode($_POST['image']);
print_r($data);
print_r(count($data));
//echo json_encode($response);

