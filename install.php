<?php
echo "Algoritmo Framework Installer\n";
if(!file_exists(getcwd().'/artisan')){
    exit("Ejecute este script desde la raíz de un proyecto Laravel.\n");
}
echo "Laravel detectado. Aquí irá la instalación del Core.\n";
