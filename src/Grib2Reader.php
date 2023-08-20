<?php

declare(strict_types=1);

namespace Okvpn\Grib2;

use FFI;
use FFI\CData;

class Grib2Reader
{
    public function read(string $filename): array
    {
        $values = $this->doRead($filename, $size);

        $result = [];
        for ($i = 0; $i < $size; $i++) {
            $result[] = $values[$i];
        }

        return $result;
    }

    /*
     *  Use only if:
     *
     * latitudeOfFirstGridPointInDegrees = 90;
     * longitudeOfFirstGridPointInDegrees = 0;
     * latitudeOfLastGridPointInDegrees = -90;
     * longitudeOfLastGridPointInDegrees = 359;
     */
    public function read2D(string $filename, float $lat1 = 90.0, float $lat2 = -90.0, float $lon1 = 0, float $lon2 = 360.0, float $step = 1.0): array
    {
        $epsilon = 0.000001;
        $step = 1/$step;
        if (fmod(180/$step, 1.0) > $epsilon) {
            throw new \LogicException("Step must be 1.0 0.5 0.25 0.125 0.1");
        }

        $Ni = (int) (360/$step);
        $Nj = (int) (180/$step) + 1;

        $lon1 = $lon1 < 0 ? 360.0 + $lon1 : $lon1;
        $lon2 = $lon2 < 0 ? 360.0 + $lon2 : $lon2;

        if (fmod($l = $lat1, $step) > $epsilon || fmod($l = $lat2, $step) > $epsilon || fmod($l = $lon1, $step) > $epsilon || fmod($l = $lon2, $step) > $epsilon) {
            throw new \LogicException("Step $step division of floating point remainder (modulo) for the latitude/longitude $l must be equals 0");
        }

        $values = $this->doRead($filename, $size);
        if (($reqSize = $Ni*$Nj) !== $size) {
            throw new \LogicException("Grib size is not match. Read $size items from file, but you pass step $step and grid $Ni x $Nj size ($reqSize items total)");
        }

        $lat1 = 90 - $lat1;
        $lat2 = 90 - $lat2;

        [$lat1, $lat2] = $lat1 > $lat2 ? [$lat2, $lat1] : [$lat1, $lat2];
        [$lon1, $lon2] = $lon1 > $lon2 ? [$lon2, $lon1] : [$lon1, $lon2];

        $Bj1 = (int) ($lat1/$step);
        $Bj2 = min((int) ($lat2/$step) + 1, $Nj);

        $Bi1 = (int) ($lon1/$step);
        $Bi2 = min((int) ($lon2/$step) + 1, $Ni);

        $loop = 0;
        $matrix = [];
        for ($j = $Bj1; $j < $Bj2; $j++) {
            for ($i = $Bi1; $i < $Bi2; $i++) {
                $matrix[$loop][] = $values[$i + $Ni*$j];
            }
            $loop += 1;
        }

        unset($values);

        return $matrix;
    }

    protected function doRead(string $filename, int &$size = null): CData|\ArrayAccess|array
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

        $ffi->codes_handle_delete($h);
        $ffi->fclose($file);
        $ffi->grib_context_delete($context);

        return $values;
    }
}
