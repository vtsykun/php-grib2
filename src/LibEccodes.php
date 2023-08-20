<?php

declare(strict_types=1);

namespace Okvpn\Grib2;

class LibEccodes
{
    protected static $ffi;

    public static $libPath = null;
    public static $tmpDir = null;

    /**
     * @return \FFI|object
     */
    public static function create(): \FFI
    {
        if (null === static::$ffi) {
            static::$tmpDir = self::$tmpDir ?? sys_get_temp_dir();
            $eccodesDir = self::$tmpDir . '/eccodes-tmp';

            static::$ffi ??= \FFI::cdef(file_get_contents(__DIR__ . '/grib2_api.h'), static::$libPath ?? __DIR__ . '/../libeccodes/libeccodes_x86.so');

            if (null === static::$libPath) {
                $dir = "$eccodesDir/eccodes/definitions";
                if  (!is_dir($eccodesDir)) {
                    mkdir($eccodesDir, 0777, true);
                    $cmd = "cd $eccodesDir; tar -xzf " . escapeshellcmd(realpath(__DIR__ . '/../libeccodes/eccodes.tar.gz')) . ' 2>&1';
                    $out = shell_exec($cmd);

                    if (!is_dir($dir)) {
                        throw new \RuntimeException("Unable to extract eccodes libs data\n$out");
                    }

                    shell_exec("chmod 777 -R '$eccodesDir'");
                }

                putenv("ECCODES_DEFINITION_PATH=$dir");
            }
        }

        return static::$ffi;
    }
}
