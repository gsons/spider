<?php
class Atomic extends Threaded {

    public function __construct($value = 0) {
        $this->value = $value;
    }

    public function inc() {
        return $this->value++;
    }

    /* ... */
    private $value;
}

class Test extends Thread {

    public function __construct(Atomic $atomic) {
        $this->atomic = $atomic;
    }

    public function run() {
        $this->atomic->inc();
//        $this->atomic->synchronized(function($atomic){
//            /* exclusive */
//            $atomic->inc();
//        }, $this->atomic);
    }

    private $atomic;
}

$atomic = new Atomic();
$threads = [];

for ($thread = 0; $thread < 2500; $thread++) {
    $threads[$thread] = new Test($atomic);
    usleep(rand(10,20));
    $threads[$thread]->start();
}

foreach ($threads as $thread)
    $thread->join();

var_dump($atomic);
?>