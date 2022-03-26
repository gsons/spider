<?php

echo PHP_EOL;
while (1){
    echo "\033[1A";
    echo date('Y-m-d H:i:s').PHP_EOL;
    echo "\033[?25l";
    usleep(50);
}