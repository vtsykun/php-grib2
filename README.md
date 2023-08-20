# PHP GRIB2 eccodes

Simple library to read GRIB2 WMO format with using FFI. 

## Purpose

This library shows an example of how to use adapt libeccodes for using with PHP FFI extension to save developers time.

## Install

```
composer require okvpn/grib2-eccodes
```

Install libaec-dev
```
apt install libaec-dev
```

(Optional) Install eccodes lib. See https://confluence.ecmwf.int/display/ECC/ecCodes+installation.

But to simplify distribution, the libeccodes is build-in with data `eccodes.tar.gz`. It will be extracted to tmp dir.

# Usage

```
# Get grib index data 
# https://www.ftp.ncep.noaa.gov/data/nccf/com/gfs/prod/gfs.<date>/<time>/atmos/gfs.t<time>z.pgrb2b.1p00.f<xxx>.idx

170:9120456:d=2023081906:RH:575 mb:3 hour fcst:
171:9195051:d=2023081906:TCDC:575 mb:3 hour fcst:
172:9235360:d=2023081906:VVEL:575 mb:3 hour fcst:
173:9325082:d=2023081906:DZDT:575 mb:3 hour fcst:
174:9400873:d=2023081906:UGRD:575 mb:3 hour fcst:
175:9480146:d=2023081906:VGRD:575 mb:3 hour fcst:
176:9560543:d=2023081906:ABSV:575 mb:3 hour fcst:
177:9632016:d=2023081906:CLWMR:575 mb:3 hour fcst:
```

Download Grib2 archive

```php
$curl = new CurlHttpClient();
// Select 174:9400873:d=2023081906:UGRD:575 mb:3 hour fcst:
// Use http 206 Partial Content feature to downlaod only one param
$res = $curl->request('GET', 'https://www.ftp.ncep.noaa.gov/data/nccf/com/gfs/prod/gfs.<date>/<time>/atmos/gfs.t<time>z.pgrb2b.1p00.f003', ['headers' => ['Range' => 'bytes=9480146-9560542']]);

file_put_contents("VGRD.p1", $res->getContent())
```

```php

require_once __DIR__ . '/vendor/autoload.php';

use FFI;
use Okvpn\Grib2\Grib2Reader;
use Okvpn\Grib2\LibEccodes;

LibEccodes::$libPath = "libeccodes.so"; // . use fill path if installed eccodes to custom path

$reader = new Grib2Reader();

$data = $reader->read("VGRD.p1");

print_r($data);
```

License
-------

MIT License.
