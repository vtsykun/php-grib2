<?php

declare(strict_types=1);

namespace Okvpn\Grib2;

use FFI;

class Grib2Reader
{
    public function read(string $filename): array
    {
        $ffi = LibEccodes::create();

        $file = $ffi->fopen($filename, 'rb');
        if ($file === null) {
            throw new \RuntimeException("Unable to open file $filename.");
        }

        $context = $ffi->grib_context_get_default();
        $error = FFI::new("int");
        $valuesSize = FFI::new("size_t");

        $h = $ffi->grib_handle_new_from_file($context, $file, FFI::addr($error));
        $ffi->codes_get_size($h, 'values', FFI::addr($valuesSize));
        $size = $valuesSize->cdata;

        $values = FFI::new("double[$size]");
        $ffi->codes_get_double_array($h, 'values', $values, FFI::addr($valuesSize));

        $result = [];
        for ($i = 0; $i < $size; $i++) {
            $result[] = $values[$i];
        }

        $ffi->codes_handle_delete($h);
        $ffi->fclose($file);
        $ffi->grib_context_delete($context);

        return $result;
    }
}
