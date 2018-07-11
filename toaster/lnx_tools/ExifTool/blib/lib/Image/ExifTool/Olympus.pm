#------------------------------------------------------------------------------
# File:         Olympus.pm
#
# Description:  Olympus/Epson EXIF maker notes tags
#
# Revisions:    12/09/2003 - P. Harvey Created
#               11/11/2004 - P. Harvey Added Epson support
#
# References:   1) http://park2.wakwak.com/~tsuruzoh/Computer/Digicams/exif-e.html
#               2) http://www.cybercom.net/~dcoffin/dcraw/
#               3) http://www.ozhiker.com/electronics/pjmt/jpeg_info/olympus_mn.html
#               4) Markku HŠnninen private communication (tests with E-1)
#               5) RŽmi Guyomarch from http://forums.dpreview.com/forums/read.asp?forum=1022&message=12790396
#               6) Frank Ledwon private communication (tests with E/C-series cameras)
#               7) Michael Meissner private communication
#               8) Shingo Noguchi, PhotoXP (http://www.daifukuya.com/photoxp/)
#               9) Mark Dapoz private communication
#              10) Lilo Huang private communication (E-330)
#              11) http://olypedia.de/Olympus_Makernotes (May 30, 2013)
#              12) Ioannis Panagiotopoulos private communication (E-510)
#              13) Chris Shaw private communication (E-3)
#              14) Viktor Lushnikov private communication (E-400)
#              15) Yrjo Rauste private communication (E-30)
#              16) Godfrey DiGiorgi private communcation (E-P1) + http://forums.dpreview.com/forums/read.asp?message=33187567
#              17) Martin Hibers private communication
#              18) Tomasz Kawecki private communication
#              19) Brad Grier private communication
#              22) Herbert Kauer private communication
#              23) Daniel Pollock private communication (PEN-F)
#              IB) Iliah Borg private communication (LibRaw)
#              NJ) Niels Kristian Bech Jensen private communication
#------------------------------------------------------------------------------

package Image::ExifTool::Olympus;

use strict;
use vars qw($VERSION);
use Image::ExifTool qw(:DataAccess :Utils);
use Image::ExifTool::Exif;
use Image::ExifTool::APP12;

$VERSION = '2.54';

sub PrintLensInfo($$$);

my %offOn = ( 0 => 'Off', 1 => 'On' );

# lookup for Olympus LensType values
# (as of ExifTool 9.15, this was the complete list of chipped lenses at www.four-thirds.org)
my %olympusLensTypes = (
    Notes => q{
        The numerical values below are given in hexadecimal.  (Prior to ExifTool
        9.15 these were in decimal.)
    },
    '0 00 00' => 'None',
    # Olympus lenses (also Kenko Tokina)
    '0 01 00' => 'Olympus Zuiko Digital ED 50mm F2.0 Macro',
    '0 01 01' => 'Olympus Zuiko Digital 40-150mm F3.5-4.5', #8
    '0 01 10' => 'Olympus M.Zuiko Digital ED 14-42mm F3.5-5.6', #PH (E-P1 pre-production)
    '0 02 00' => 'Olympus Zuiko Digital ED 150mm F2.0',
    '0 02 10' => 'Olympus M.Zuiko Digital 17mm F2.8 Pancake', #PH (E-P1 pre-production)
    '0 03 00' => 'Olympus Zuiko Digital ED 300mm F2.8',
    '0 03 10' => 'Olympus M.Zuiko Digital ED 14-150mm F4.0-5.6 [II]', #11 (The second version of this lens seems to have the same lens ID number as the first version #NJ)
    '0 04 10' => 'Olympus M.Zuiko Digital ED 9-18mm F4.0-5.6', #11
    '0 05 00' => 'Olympus Zuiko Digital 14-54mm F2.8-3.5',
    '0 05 01' => 'Olympus Zuiko Digital Pro ED 90-250mm F2.8', #9
    '0 05 10' => 'Olympus M.Zuiko Digital ED 14-42mm F3.5-5.6 L', #11 (E-PL1)
    '0 06 00' => 'Olympus Zuiko Digital ED 50-200mm F2.8-3.5',
    '0 06 01' => 'Olympus Zuiko Digital ED 8mm F3.5 Fisheye', #9
    '0 06 10' => 'Olympus M.Zuiko Digital ED 40-150mm F4.0-5.6', #PH
    '0 07 00' => 'Olympus Zuiko Digital 11-22mm F2.8-3.5',
    '0 07 01' => 'Olympus Zuiko Digital 18-180mm F3.5-6.3', #6
    '0 07 10' => 'Olympus M.Zuiko Digital ED 12mm F2.0', #PH
    '0 08 01' => 'Olympus Zuiko Digital 70-300mm F4.0-5.6', #7 (seen as release 1 - PH)
    '0 08 10' => 'Olympus M.Zuiko Digital ED 75-300mm F4.8-6.7', #PH
    '0 09 10' => 'Olympus M.Zuiko Digital 14-42mm F3.5-5.6 II', #PH (E-PL2)
    '0 10 01' => 'Kenko Tokina Reflex 300mm F6.3 MF Macro', #NJ
    '0 10 10' => 'Olympus M.Zuiko Digital ED 12-50mm F3.5-6.3 EZ', #PH
    '0 11 10' => 'Olympus M.Zuiko Digital 45mm F1.8', #17
    '0 12 10' => 'Olympus M.Zuiko Digital ED 60mm F2.8 Macro', #NJ
    '0 13 10' => 'Olympus M.Zuiko Digital 14-42mm F3.5-5.6 II R', #PH/NJ
    '0 14 10' => 'Olympus M.Zuiko Digital ED 40-150mm F4.0-5.6 R', #19
  # '0 14 10.1' => 'Olympus M.Zuiko Digital ED 14-150mm F4.0-5.6 II', #11 (questionable & unconfirmed -- all samples I can find are '0 3 10' - PH)
    '0 15 00' => 'Olympus Zuiko Digital ED 7-14mm F4.0',
    '0 15 10' => 'Olympus M.Zuiko Digital ED 75mm F1.8', #PH
    '0 16 10' => 'Olympus M.Zuiko Digital 17mm F1.8', #NJ
    '0 17 00' => 'Olympus Zuiko Digital Pro ED 35-100mm F2.0', #7
    '0 18 00' => 'Olympus Zuiko Digital 14-45mm F3.5-5.6',
    '0 18 10' => 'Olympus M.Zuiko Digital ED 75-300mm F4.8-6.7 II', #NJ
    '0 19 10' => 'Olympus M.Zuiko Digital ED 12-40mm F2.8 Pro', #PH
    '0 20 00' => 'Olympus Zuiko Digital 35mm F3.5 Macro', #9
    '0 20 10' => 'Olympus M.Zuiko Digital ED 40-150mm F2.8 Pro', #NJ
    '0 21 10' => 'Olympus M.Zuiko Digital ED 14-42mm F3.5-5.6 EZ', #NJ
    '0 22 00' => 'Olympus Zuiko Digital 17.5-45mm F3.5-5.6', #9
    '0 22 10' => 'Olympus M.Zuiko Digital 25mm F1.8', #NJ
    '0 23 00' => 'Olympus Zuiko Digital ED 14-42mm F3.5-5.6', #PH
    '0 23 10' => 'Olympus M.Zuiko Digital ED 7-14mm F2.8 Pro', #NJ
    '0 24 00' => 'Olympus Zuiko Digital ED 40-150mm F4.0-5.6', #PH
    '0 24 10' => 'Olympus M.Zuiko Digital ED 300mm F4.0 IS Pro', #NJ
    '0 25 10' => 'Olympus M.Zuiko Digital ED 8mm F1.8 Fisheye Pro', #NJ
    '0 26 10' => 'Olympus M.Zuiko Digital ED 12-100mm F4.0 IS Pro', #IB/NJ
    '0 27 10' => 'Olympus M.Zuiko Digital ED 30mm F3.5 Macro', #IB/NJ
    '0 28 10' => 'Olympus M.Zuiko Digital ED 25mm F1.2 Pro', #IB/NJ
    '0 29 10' => 'Olympus M.Zuiko Digital ED 17mm F1.2 Pro', #IB
    '0 30 00' => 'Olympus Zuiko Digital ED 50-200mm F2.8-3.5 SWD', #7
    '0 30 10' => 'Olympus M.Zuiko Digital ED 45mm F1.2 Pro', #IB
    '0 31 00' => 'Olympus Zuiko Digital ED 12-60mm F2.8-4.0 SWD', #7
    '0 32 00' => 'Olympus Zuiko Digital ED 14-35mm F2.0 SWD', #PH
    '0 33 00' => 'Olympus Zuiko Digital 25mm F2.8', #PH
    '0 34 00' => 'Olympus Zuiko Digital ED 9-18mm F4.0-5.6', #7
    '0 35 00' => 'Olympus Zuiko Digital 14-54mm F2.8-3.5 II', #PH
    # Sigma lenses
    '1 01 00' => 'Sigma 18-50mm F3.5-5.6 DC', #8
    '1 01 10' => 'Sigma 30mm F2.8 EX DN', #NJ
    '1 02 00' => 'Sigma 55-200mm F4.0-5.6 DC',
    '1 02 10' => 'Sigma 19mm F2.8 EX DN', #NJ
    '1 03 00' => 'Sigma 18-125mm F3.5-5.6 DC',
    '1 03 10' => 'Sigma 30mm F2.8 DN | A', #NJ
    '1 04 00' => 'Sigma 18-125mm F3.5-5.6 DC', #7
    '1 04 10' => 'Sigma 19mm F2.8 DN | A', #NJ
    '1 05 00' => 'Sigma 30mm F1.4 EX DC HSM', #10
    '1 05 10' => 'Sigma 60mm F2.8 DN | A', #NJ
    '1 06 00' => 'Sigma APO 50-500mm F4.0-6.3 EX DG HSM', #6
    '1 06 10' => 'Sigma 30mm F1.4 DC DN | C', #NJ
    '1 07 00' => 'Sigma Macro 105mm F2.8 EX DG', #PH
    '1 07 10' => 'Sigma 16mm F1.4 DC DN | C (017)', #IB
    '1 08 00' => 'Sigma APO Macro 150mm F2.8 EX DG HSM', #PH
    '1 09 00' => 'Sigma 18-50mm F2.8 EX DC Macro', #NJ
    '1 10 00' => 'Sigma 24mm F1.8 EX DG Aspherical Macro', #PH
    '1 11 00' => 'Sigma APO 135-400mm F4.5-5.6 DG', #11
    '1 12 00' => 'Sigma APO 300-800mm F5.6 EX DG HSM', #11
    '1 13 00' => 'Sigma 30mm F1.4 EX DC HSM', #11
    '1 14 00' => 'Sigma APO 50-500mm F4.0-6.3 EX DG HSM', #11
    '1 15 00' => 'Sigma 10-20mm F4.0-5.6 EX DC HSM', #11
    '1 16 00' => 'Sigma APO 70-200mm F2.8 II EX DG Macro HSM', #11
    '1 17 00' => 'Sigma 50mm F1.4 EX DG HSM', #11
    # Panasonic/Leica lenses
    '2 01 00' => 'Leica D Vario Elmarit 14-50mm F2.8-3.5 Asph.', #11
    '2 01 10' => 'Lumix G Vario 14-45mm F3.5-5.6 Asph. Mega OIS', #16
    '2 02 00' => 'Leica D Summilux 25mm F1.4 Asph.', #11
    '2 02 10' => 'Lumix G Vario 45-200mm F4.0-5.6 Mega OIS', #16
    '2 03 00' => 'Leica D Vario Elmar 14-50mm F3.8-5.6 Asph. Mega OIS', #11
    '2 03 01' => 'Leica D Vario Elmar 14-50mm F3.8-5.6 Asph.', #14 (L10 kit)
    '2 03 10' => 'Lumix G Vario HD 14-140mm F4.0-5.8 Asph. Mega OIS', #16
    '2 04 00' => 'Leica D Vario Elmar 14-150mm F3.5-5.6', #13
    '2 04 10' => 'Lumix G Vario 7-14mm F4.0 Asph.', #PH (E-P1 pre-production)
    '2 05 10' => 'Lumix G 20mm F1.7 Asph.', #16
    '2 06 10' => 'Leica DG Macro-Elmarit 45mm F2.8 Asph. Mega OIS', #PH
    '2 07 10' => 'Lumix G Vario 14-42mm F3.5-5.6 Asph. Mega OIS', #NJ
    '2 08 10' => 'Lumix G Fisheye 8mm F3.5', #PH
    '2 09 10' => 'Lumix G Vario 100-300mm F4.0-5.6 Mega OIS', #11
    '2 10 10' => 'Lumix G 14mm F2.5 Asph.', #17
    '2 11 10' => 'Lumix G 12.5mm F12 3D', #NJ (H-FT012)
    '2 12 10' => 'Leica DG Summilux 25mm F1.4 Asph.', #NJ
    '2 13 10' => 'Lumix G X Vario PZ 45-175mm F4.0-5.6 Asph. Power OIS', #NJ
    '2 14 10' => 'Lumix G X Vario PZ 14-42mm F3.5-5.6 Asph. Power OIS', #NJ
    '2 15 10' => 'Lumix G X Vario 12-35mm F2.8 Asph. Power OIS', #PH
    '2 16 10' => 'Lumix G Vario 45-150mm F4.0-5.6 Asph. Mega OIS', #NJ
    '2 17 10' => 'Lumix G X Vario 35-100mm F2.8 Power OIS', #PH
    '2 18 10' => 'Lumix G Vario 14-42mm F3.5-5.6 II Asph. Mega OIS', #NJ
    '2 19 10' => 'Lumix G Vario 14-140mm F3.5-5.6 Asph. Power OIS', #NJ
    '2 20 10' => 'Lumix G Vario 12-32mm F3.5-5.6 Asph. Mega OIS', #NJ
    '2 21 10' => 'Leica DG Nocticron 42.5mm F1.2 Asph. Power OIS', #NJ
    '2 22 10' => 'Leica DG Summilux 15mm F1.7 Asph.', #NJ
    '2 23 10' => 'Lumix G Vario 35-100mm F4.0-5.6 Asph. Mega OIS', #NJ
    '2 24 10' => 'Lumix G Macro 30mm F2.8 Asph. Mega OIS', #NJ
    '2 25 10' => 'Lumix G 42.5mm F1.7 Asph. Power OIS', #NJ
    '2 26 10' => 'Lumix G 25mm F1.7 Asph.', #NJ
    '2 27 10' => 'Leica DG Vario-Elmar 100-400mm F4.0-6.3 Asph. Power OIS', #NJ
    '2 28 10' => 'Lumix G Vario 12-60mm F3.5-5.6 Asph. Power OIS', #NJ
    '3 01 00' => 'Leica D Vario Elmarit 14-50mm F2.8-3.5 Asph.', #11
    '3 02 00' => 'Leica D Summilux 25mm F1.4 Asph.', #11
    # Tamron lenses
    '5 01 10' => 'Tamron 14-150mm F3.5-5.8 Di III', #NJ (model C001)
);

# lookup for Olympus camera types (ref PH)
my %olympusCameraTypes = (
    Notes => q{
        These values are currently decoded only for Olympus models.  Models with
        Olympus-style maker notes from other brands such as Acer, BenQ, Hitachi, HP,
        Premier, Konica-Minolta, Maginon, Ricoh, Rollei, SeaLife, Sony, Supra,
        Vivitar are not listed.
    },
    D4028 => 'X-2,C-50Z',
    D4029 => 'E-20,E-20N,E-20P',
    D4034 => 'C720UZ',
    D4040 => 'E-1',
    D4041 => 'E-300',
    D4083 => 'C2Z,D520Z,C220Z',
    D4106 => 'u20D,S400D,u400D',
    D4120 => 'X-1',
    D4122 => 'u10D,S300D,u300D',
    D4125 => 'AZ-1',
    D4141 => 'C150,D390',
    D4193 => 'C-5000Z',
    D4194 => 'X-3,C-60Z',
    D4199 => 'u30D,S410D,u410D',
    D4205 => 'X450,D535Z,C370Z',
    D4210 => 'C160,D395',
    D4211 => 'C725UZ',
    D4213 => 'FerrariMODEL2003',
    D4216 => 'u15D',
    D4217 => 'u25D',
    D4220 => 'u-miniD,Stylus V',
    D4221 => 'u40D,S500,uD500',
    D4231 => 'FerrariMODEL2004',
    D4240 => 'X500,D590Z,C470Z',
    D4244 => 'uD800,S800',
    D4256 => 'u720SW,S720SW',
    D4261 => 'X600,D630,FE5500',
    D4262 => 'uD600,S600',
    D4301 => 'u810/S810', # (yes, "/".  Olympus is not consistent in the notation)
    D4302 => 'u710,S710',
    D4303 => 'u700,S700',
    D4304 => 'FE100,X710',
    D4305 => 'FE110,X705',
    D4310 => 'FE-130,X-720',
    D4311 => 'FE-140,X-725',
    D4312 => 'FE150,X730',
    D4313 => 'FE160,X735',
    D4314 => 'u740,S740',
    D4315 => 'u750,S750',
    D4316 => 'u730/S730',
    D4317 => 'FE115,X715',
    D4321 => 'SP550UZ',
    D4322 => 'SP510UZ',
    D4324 => 'FE170,X760',
    D4326 => 'FE200',
    D4327 => 'FE190/X750', # (also SX876)
    D4328 => 'u760,S760',
    D4330 => 'FE180/X745', # (also SX875)
    D4331 => 'u1000/S1000',
    D4332 => 'u770SW,S770SW',
    D4333 => 'FE240/X795',
    D4334 => 'FE210,X775',
    D4336 => 'FE230/X790',
    D4337 => 'FE220,X785',
    D4338 => 'u725SW,S725SW',
    D4339 => 'FE250/X800',
    D4341 => 'u780,S780',
    D4343 => 'u790SW,S790SW',
    D4344 => 'u1020,S1020',
    D4346 => 'FE15,X10',
    D4348 => 'FE280,X820,C520',
    D4349 => 'FE300,X830',
    D4350 => 'u820,S820',
    D4351 => 'u1200,S1200',
    D4352 => 'FE270,X815,C510',
    D4353 => 'u795SW,S795SW',
    D4354 => 'u1030SW,S1030SW',
    D4355 => 'SP560UZ',
    D4356 => 'u1010,S1010',
    D4357 => 'u830,S830',
    D4359 => 'u840,S840',
    D4360 => 'FE350WIDE,X865',
    D4361 => 'u850SW,S850SW',
    D4362 => 'FE340,X855,C560',
    D4363 => 'FE320,X835,C540',
    D4364 => 'SP570UZ',
    D4366 => 'FE330,X845,C550',
    D4368 => 'FE310,X840,C530',
    D4370 => 'u1050SW,S1050SW',
    D4371 => 'u1060,S1060',
    D4372 => 'FE370,X880,C575',
    D4374 => 'SP565UZ',
    D4377 => 'u1040,S1040',
    D4378 => 'FE360,X875,C570',
    D4379 => 'FE20,X15,C25',
    D4380 => 'uT6000,ST6000',
    D4381 => 'uT8000,ST8000',
    D4382 => 'u9000,S9000',
    D4384 => 'SP590UZ',
    D4385 => 'FE3010,X895',
    D4386 => 'FE3000,X890',
    D4387 => 'FE35,X30',
    D4388 => 'u550WP,S550WP',
    D4390 => 'FE5000,X905',
    D4391 => 'u5000',
    D4392 => 'u7000,S7000',
    D4396 => 'FE5010,X915',
    D4397 => 'FE25,X20',
    D4398 => 'FE45,X40',
    D4401 => 'XZ-1',
    D4402 => 'uT6010,ST6010',
    D4406 => 'u7010,S7010 / u7020,S7020',
    D4407 => 'FE4010,X930',
    D4408 => 'X560WP',
    D4409 => 'FE26,X21',
    D4410 => 'FE4000,X920,X925',
    D4411 => 'FE46,X41,X42',
    D4412 => 'FE5020,X935',
    D4413 => 'uTough-3000',
    D4414 => 'StylusTough-6020',
    D4415 => 'StylusTough-8010',
    D4417 => 'u5010,S5010',
    D4418 => 'u7040,S7040',
    D4419 => 'u9010,S9010',
    D4423 => 'FE4040',
    D4424 => 'FE47,X43',
    D4426 => 'FE4030,X950',
    D4428 => 'FE5030,X965,X960',
    D4430 => 'u7030,S7030',
    D4432 => 'SP600UZ',
    D4434 => 'SP800UZ',
    D4439 => 'FE4020,X940',
    D4442 => 'FE5035',
    D4448 => 'FE4050,X970',
    D4450 => 'FE5050,X985',
    D4454 => 'u-7050',
    D4464 => 'T10,X27',
    D4470 => 'FE5040,X980',
    D4472 => 'TG-310',
    D4474 => 'TG-610',
    D4476 => 'TG-810',
    D4478 => 'VG145,VG140,D715',
    D4479 => 'VG130,D710',
    D4480 => 'VG120,D705',
    D4482 => 'VR310,D720',
    D4484 => 'VR320,D725',
    D4486 => 'VR330,D730',
    D4488 => 'VG110,D700',
    D4490 => 'SP-610UZ',
    D4492 => 'SZ-10',
    D4494 => 'SZ-20',
    D4496 => 'SZ-30MR',
    D4498 => 'SP-810UZ',
    D4500 => 'SZ-11',
    D4504 => 'TG-615',
    D4508 => 'TG-620',
    D4510 => 'TG-820',
    D4512 => 'TG-1',
    D4516 => 'SH-21',
    D4519 => 'SZ-14',
    D4520 => 'SZ-31MR',
    D4521 => 'SH-25MR',
    D4523 => 'SP-720UZ',
    D4529 => 'VG170',
    D4531 => 'XZ-2',
    D4535 => 'SP-620UZ',
    D4536 => 'TG-320',
    D4537 => 'VR340,D750',
    D4538 => 'VG160,X990,D745',
    D4541 => 'SZ-12',
    D4545 => 'VH410',
    D4546 => 'XZ-10', #IB
    D4547 => 'TG-2',
    D4548 => 'TG-830',
    D4549 => 'TG-630',
    D4550 => 'SH-50',
    D4553 => 'SZ-16,DZ-105',
    D4562 => 'SP-820UZ',
    D4566 => 'SZ-15',
    D4572 => 'STYLUS1',
    D4574 => 'TG-3',
    D4575 => 'TG-850',
    D4579 => 'SP-100EE',
    D4580 => 'SH-60',
    D4581 => 'SH-1',
    D4582 => 'TG-835',
    D4585 => 'SH-2 / SH-3',
    D4586 => 'TG-4',
    D4587 => 'TG-860',
    D4591 => 'TG-870',
    D4593 => 'TG-5', #IB
    D4809 => 'C2500L',
    D4842 => 'E-10',
    D4856 => 'C-1',
    D4857 => 'C-1Z,D-150Z',
    DCHC => 'D500L',
    DCHT => 'D600L / D620L',
    K0055 => 'AIR-A01',
    S0003 => 'E-330',
    S0004 => 'E-500',
    S0009 => 'E-400',
    S0010 => 'E-510',
    S0011 => 'E-3',
    S0013 => 'E-410',
    S0016 => 'E-420',
    S0017 => 'E-30',
    S0018 => 'E-520',
    S0019 => 'E-P1',
    S0023 => 'E-620',
    S0026 => 'E-P2',
    S0027 => 'E-PL1',
    S0029 => 'E-450',
    S0030 => 'E-600',
    S0032 => 'E-P3',
    S0033 => 'E-5',
    S0034 => 'E-PL2',
    S0036 => 'E-M5',
    S0038 => 'E-PL3',
    S0039 => 'E-PM1',
    S0040 => 'E-PL1s',
    S0042 => 'E-PL5',
    S0043 => 'E-PM2',
    S0044 => 'E-P5',
    S0045 => 'E-PL6',
    S0046 => 'E-PL7', #IB
    S0047 => 'E-M1',
    S0051 => 'E-M10',
    S0052 => 'E-M5MarkII', #IB
    S0059 => 'E-M10MarkII',
    S0061 => 'PEN-F', #forum7005
    S0065 => 'E-PL8',
    S0067 => 'E-M1MarkII',
    S0068 => 'E-M10MarkIII',
    S0076 => 'E-PL9', #IB
    SR45 => 'D220',
    SR55 => 'D320L',
    SR83 => 'D340L',
    SR85 => 'C830L,D340R',
    SR852 => 'C860L,D360L',
    SR872 => 'C900Z,D400Z',
    SR874 => 'C960Z,D460Z',
    SR951 => 'C2000Z',
    SR952 => 'C21',
    SR953 => 'C21T.commu',
    SR954 => 'C2020Z',
    SR955 => 'C990Z,D490Z',
    SR956 => 'C211Z',
    SR959 => 'C990ZS,D490Z',
    SR95A => 'C2100UZ',
    SR971 => 'C100,D370',
    SR973 => 'C2,D230',
    SX151 => 'E100RS',
    SX351 => 'C3000Z / C3030Z',
    SX354 => 'C3040Z',
    SX355 => 'C2040Z',
    SX357 => 'C700UZ',
    SX358 => 'C200Z,D510Z',
    SX374 => 'C3100Z,C3020Z',
    SX552 => 'C4040Z',
    SX553 => 'C40Z,D40Z',
    SX556 => 'C730UZ',
    SX558 => 'C5050Z',
    SX571 => 'C120,D380',
    SX574 => 'C300Z,D550Z',
    SX575 => 'C4100Z,C4000Z',
    SX751 => 'X200,D560Z,C350Z',
    SX752 => 'X300,D565Z,C450Z',
    SX753 => 'C750UZ',
    SX754 => 'C740UZ',
    SX755 => 'C755UZ',
    SX756 => 'C5060WZ',
    SX757 => 'C8080WZ',
    SX758 => 'X350,D575Z,C360Z',
    SX759 => 'X400,D580Z,C460Z',
    SX75A => 'AZ-2ZOOM',
    SX75B => 'D595Z,C500Z',
    SX75C => 'X550,D545Z,C480Z',
    SX75D => 'IR-300',
    SX75F => 'C55Z,C5500Z',
    SX75G => 'C170,D425',
    SX75J => 'C180,D435',
    SX771 => 'C760UZ',
    SX772 => 'C770UZ',
    SX773 => 'C745UZ',
    SX774 => 'X250,D560Z,C350Z',
    SX775 => 'X100,D540Z,C310Z',
    SX776 => 'C460ZdelSol',
    SX777 => 'C765UZ',
    SX77A => 'D555Z,C315Z',
    SX851 => 'C7070WZ',
    SX852 => 'C70Z,C7000Z',
    SX853 => 'SP500UZ',
    SX854 => 'SP310',
    SX855 => 'SP350',
    SX873 => 'SP320',
    SX875 => 'FE180/X745', # (also D4330)
    SX876 => 'FE190/X750', # (also D4327)
#   other brands
#    4MP9Q3 => 'Camera 4MP-9Q3'
#    4MP9T2 => 'BenQ DC C420 / Camera 4MP-9T2'
#    5MP9Q3 => 'Camera 5MP-9Q3',
#    5MP9X9 => 'Camera 5MP-9X9',
#   '5MP-9T'=> 'Camera 5MP-9T3',
#   '5MP-9Y'=> 'Camera 5MP-9Y2',
#   '6MP-9U'=> 'Camera 6MP-9U9',
#    7MP9Q3 => 'Camera 7MP-9Q3',
#   '8MP-9U'=> 'Camera 8MP-9U4',
#    CE5330 => 'Acer CE-5330',
#   'CP-853'=> 'Acer CP-8531',
#    CS5531 => 'Acer CS5531',
#    DC500  => 'SeaLife DC500',
#    DC7370 => 'Camera 7MP-9GA',
#    DC7371 => 'Camera 7MP-9GM',
#    DC7371 => 'Hitachi HDC-751E',
#    DC7375 => 'Hitachi HDC-763E / Rollei RCP-7330X / Ricoh Caplio RR770 / Vivitar ViviCam 7330',
#   'DC E63'=> 'BenQ DC E63+',
#   'DC P86'=> 'BenQ DC P860',
#    DS5340 => 'Maginon Performic S5 / Premier 5MP-9M7',
#    DS5341 => 'BenQ E53+ / Supra TCM X50 / Maginon X50 / Premier 5MP-9P8',
#    DS5346 => 'Premier 5MP-9Q2',
#    E500   => 'Konica Minolta DiMAGE E500',
#    MAGINO => 'Maginon X60',
#    Mz60   => 'HP Photosmart Mz60',
#    Q3DIGI => 'Camera 5MP-9Q3',
#    SLIMLI => 'Supra Slimline X6',
#    V8300s => 'Vivitar V8300s',
);

# ArtFilter, ArtFilterEffect and MagicFilter values (ref PH)
my %filters = (
    0 => 'Off',
    1 => 'Soft Focus', # (XZ-1)
    2 => 'Pop Art', # (SZ-10 magic filter 1,SZ-31MR,E-M5,E-PL3)
    3 => 'Pale & Light Color',
    4 => 'Light Tone',
    5 => 'Pin Hole', # (SZ-10 magic filter 2,SZ-31MR,E-PL3)
    6 => 'Grainy Film',
    9 => 'Diorama',
    10 => 'Cross Process',
    12 => 'Fish Eye', # (SZ-10 magic filter 3)
    13 => 'Drawing', # (SZ-10 magic filter 4)
    14 => 'Gentle Sepia', # (E-5)
    15 => 'Pale & Light Color II', #forum6269 ('Tender Light' ref 11)
    16 => 'Pop Art II', #11 (E-PL3 "(dark)" - PH)
    17 => 'Pin Hole II', #11 (E-PL3 "(color 2)" - PH)
    18 => 'Pin Hole III', #11 (E-M5, E-PL3 "(color 3)" - PH)
    19 => 'Grainy Film II', #11
    20 => 'Dramatic Tone', # (XZ-1,SZ-31MR)
    21 => 'Punk', # (SZ-10 magic filter 6)
    22 => 'Soft Focus 2', # (SZ-10 magic filter 5)
    23 => 'Sparkle', # (SZ-10 magic filter 7)
    24 => 'Watercolor', # (SZ-10 magic filter 8)
    25 => 'Key Line', # (E-M5)
    26 => 'Key Line II', #forum6269
    27 => 'Miniature', # (SZ-31MR)
    28 => 'Reflection', # (TG-820,SZ-31MR)
    29 => 'Fragmented', # (TG-820,SZ-31MR)
    31 => 'Cross Process II', #forum6269
    32 => 'Dramatic Tone II',  #forum6269 (Dramatic Tone B&W for E-M5)
    33 => 'Watercolor I', # ('Watercolor I' for EM-1 ref forum6269, 'Watercolor II' for E-PM2 ref PH)
    34 => 'Watercolor II', #forum6269
    35 => 'Diorama II', #forum6269
    36 => 'Vintage', #forum6269
    37 => 'Vintage II', #forum6269
    38 => 'Vintage III', #forum6269
    39 => 'Partial Color', #forum6269
    40 => 'Partial Color II', #forum6269
    41 => 'Partial Color III', #forum6269
);

my %toneLevelType = (
    0 => '0',
    -31999 => 'Highlights',
    -31998 => 'Shadows',
    -31997 => 'Midtones',
);

# tag information for WAV "Index" tags
my %indexInfo = (
    Format => 'int32u',
    RawConv => '$val == 0xffffffff ? undef : $val',
    ValueConv => '$val / 1000',
    PrintConv => 'ConvertDuration($val)',
);

# Olympus tags
%Image::ExifTool::Olympus::Main = (
    WRITE_PROC => \&Image::ExifTool::Exif::WriteExif,
    CHECK_PROC => \&Image::ExifTool::Exif::CheckExif,
    WRITABLE => 1,
    GROUPS => { 0 => 'MakerNotes', 2 => 'Camera' },
#
# Tags 0x0000 through 0x0103 are the same as Konica/Minolta cameras (ref 3)
# (removed 0x0101-0x0103 because they weren't supported by my samples - PH)
#
    0x0000 => {
        Name => 'MakerNoteVersion',
        Writable => 'undef',
    },
    0x0001 => {
        Name => 'MinoltaCameraSettingsOld',
        SubDirectory => {
            TagTable => 'Image::ExifTool::Minolta::CameraSettings',
            ByteOrder => 'BigEndian',
        },
    },
    0x0003 => {
        Name => 'MinoltaCameraSettings',
        SubDirectory => {
            TagTable => 'Image::ExifTool::Minolta::CameraSettings',
            ByteOrder => 'BigEndian',
        },
    },
    0x0040 => {
        Name => 'CompressedImageSize',
        Writable => 'int32u',
    },
    0x0081 => {
        Name => 'PreviewImageData',
        Binary => 1,
        Writable => 0,
    },
    0x0088 => {
        Name => 'PreviewImageStart',
        Flags => 'IsOffset',
        OffsetPair => 0x0089, # point to associated byte count
        DataTag => 'PreviewImage',
        Writable => 0,
        Protected => 2,
    },
    0x0089 => {
        Name => 'PreviewImageLength',
        OffsetPair => 0x0088, # point to associated offset
        DataTag => 'PreviewImage',
        Writable => 0,
        Protected => 2,
    },
    0x0100 => {
        Name => 'ThumbnailImage',
        Groups => { 2 => 'Preview' },
        Writable => 'undef',
        WriteCheck => '$self->CheckImage(\$val)',
        Binary => 1,
    },
    0x0104 => { Name => 'BodyFirmwareVersion',    Writable => 'string' }, #11
#
# end Konica/Minolta tags
#
    0x0200 => {
        Name => 'SpecialMode',
        Notes => q{
            3 numbers: 1. Shooting mode: 0=Normal, 2=Fast, 3=Panorama;
            2. Sequence Number; 3. Panorama Direction: 1=Left-right,
            2=Right-left, 3=Bottom-Top, 4=Top-Bottom
        },
        Writable => 'int32u',
        Count => 3,
        PrintConv => sub { #3
            my $val = shift;
            my @v = split ' ', $val;
            return $val unless @v >= 3;
            my @v0 = ('Normal','Unknown (1)','Fast','Panorama');
            my @v2 = ('(none)','Left to Right','Right to Left','Bottom to Top','Top to Bottom');
            $val = $v0[$v[0]] || "Unknown ($v[0])";
            $val .= ", Sequence: $v[1]";
            $val .= ', Panorama: ' . ($v2[$v[2]] || "Unknown ($v[2])");
            return $val;
        },
    },
    0x0201 => {
        Name => 'Quality',
        Writable => 'int16u',
        Notes => q{
            Quality values are decoded based on the CameraType tag. All types
            represent SQ, HQ and SHQ as sequential integers, but in general
            SX-type cameras start with a value of 0 for SQ while others start
            with 1
        },
        # These values are different for different camera types
        # (can't have Condition based on CameraType because it isn't known
        #  when this tag is extracted)
        PrintConv => sub {
            my ($val, $self) = @_;
            my %t1 = ( # all SX camera types except SX151
                0 => 'SQ (Low)',
                1 => 'HQ (Normal)',
                2 => 'SHQ (Fine)',
                6 => 'RAW', #PH - C5050WZ
            );
            my %t2 = ( # all other types (except D4322, ref 22)
                1 => 'SQ (Low)',
                2 => 'HQ (Normal)',
                3 => 'SHQ (Fine)',
                4 => 'RAW',
                5 => 'Medium-Fine', #PH
                6 => 'Small-Fine', #PH
                33 => 'Uncompressed', #PH - C2100Z
            );
            my $conv = $self->{CameraType} =~ /^(SX(?!151\b)|D4322)/ ? \%t1 : \%t2;
            return $$conv{$val} ? $$conv{$val} : "Unknown ($val)";
        },
        # (no PrintConvInv because we don't know CameraType at write time)
    },
    0x0202 => {
        Name => 'Macro',
        Writable => 'int16u',
        PrintConv => {
            0 => 'Off',
            1 => 'On',
            2 => 'Super Macro', #6
        },
    },
    0x0203 => { #6
        Name => 'BWMode',
        Description => 'Black And White Mode',
        Writable => 'int16u',
        PrintConv => {
            0 => 'Off',
            1 => 'On',
            6 => '(none)', #22
        },
    },
    0x0204 => {
        Name => 'DigitalZoom',
        Writable => 'rational64u',
        PrintConv => '$val=~/\./ or $val.=".0"; $val',
        PrintConvInv => '$val',
    },
    0x0205 => { #6
        Name => 'FocalPlaneDiagonal',
        Writable => 'rational64u',
        PrintConv => '"$val mm"',
        PrintConvInv => '$val=~s/\s*mm$//;$val',
    },
    0x0206 => { Name => 'LensDistortionParams', Writable => 'int16s', Count => 6 }, #6
    0x0207 => { #PH (was incorrectly FirmwareVersion, ref 1/3)
        Name => 'CameraType',
        Condition => '$$valPt ne "NORMAL"', # FE240, SP510, u730 and u1000 write this
        Writable => 'string',
        DataMember => 'CameraType',
        RawConv => '$self->{CameraType} = $val',
        SeparateTable => 'CameraType',
        ValueConv => '$val =~ s/\s+$//; $val',  # ("SX151 " has trailing space)
        ValueConvInv => '$val',
        PrintConv => \%olympusCameraTypes,
        Priority => 0,
        # 'NORMAL' for some models: u730,SP510UZ,u1000,FE240
    },
    0x0208 => {
        Name => 'TextInfo',
        SubDirectory => {
            TagTable => 'Image::ExifTool::Olympus::TextInfo',
        },
    },
    0x0209 => {
        Name => 'CameraID',
        Format => 'string', # this really should have been a string
    },
    0x020b => { Name => 'EpsonImageWidth',  Writable => 'int16u' }, #PH
    0x020c => { Name => 'EpsonImageHeight', Writable => 'int16u' }, #PH
    0x020d => { Name => 'EpsonSoftware',    Writable => 'string' }, #PH
    0x0280 => { #PH
        %Image::ExifTool::previewImageTagInfo,
        Groups => { 2 => 'Preview' },
        Notes => 'found in ERF and JPG images from some Epson models',
        Format => 'undef',
        Writable => 'int8u',
    },
    0x0300 => { Name => 'PreCaptureFrames', Writable => 'int16u' }, #6
    0x0301 => { Name => 'WhiteBoard',       Writable => 'int16u' }, #11
    0x0302 => { #6
        Name => 'OneTouchWB',
        Writable => 'int16u',
        PrintConv => {
            0 => 'Off',
            1 => 'On',
            2 => 'On (Preset)',
        },
    },
    0x0303 => { Name => 'WhiteBalanceBracket',  Writable => 'int16u' }, #11
    0x0304 => { Name => 'WhiteBalanceBias',     Writable => 'int16u' }, #11
   # 0x0305 => 'PrintMatching', ? #11
    0x0401 => { #IB
        Name => 'BlackLevel',
        Condition => '$format eq "int32u" and $count == 4',
        Writable => 'int32u',
        Count => 4,
        Notes => 'found in Epson ERF images',
    },
    # 0x0402 - BitCodedAutoFocus (ref 11)
    0x0403 => { #11
        Name => 'SceneMode',
        Writable => 'int16u',
        PrintConvColumns => 2,
        PrintConv => {
            0 => 'Normal',
            1 => 'Standard',
            2 => 'Auto',
            3 => 'Intelligent Auto', #PH (guess, u7040)
            4 => 'Portrait',
            5 => 'Landscape+Portrait',
            6 => 'Landscape',
            7 => 'Night Scene',
            8 => 'Night+Portrait',
            9 => 'Sport',
            10 => 'Self Portrait',
            11 => 'Indoor',
            12 => 'Beach & Snow',
            13 => 'Beach',
            14 => 'Snow',
            15 => 'Self Portrait+Self Timer',
            16 => 'Sunset',
            17 => 'Cuisine',
            18 => 'Documents',
            19 => 'Candle',
            20 => 'Fireworks',
            21 => 'Available Light',
            22 => 'Vivid',
            23 => 'Underwater Wide1',
            24 => 'Underwater Macro',
            25 => 'Museum',
            26 => 'Behind Glass',
            27 => 'Auction',
            28 => 'Shoot & Select1',
            29 => 'Shoot & Select2',
            30 => 'Underwater Wide2',
            31 => 'Digital Image Stabilization',
            32 => 'Face Portrait',
            33 => 'Pet',
            34 => 'Smile Shot',
            35 => 'Quick Shutter',
            43 => 'Hand-held Starlight', #PH (SH-21)
            100 => 'Panorama', #PH (SH-21)
            101 => 'Magic Filter', #PH
            103 => 'HDR', #PH (XZ-2)
        },
    },
    0x0404 => { Name => 'SerialNumber', Writable => 'string' }, #PH (D595Z, C7070WZ)
    0x0405 => { Name => 'Firmware',     Writable => 'string' }, #11
    0x0e00 => { # (AFFieldCoord for models XZ-2 and XZ-10, ref 11)
        Name => 'PrintIM',
        Description => 'Print Image Matching',
        Writable => 0,
        SubDirectory => {
            TagTable => 'Image::ExifTool::PrintIM::Main',
        },
    },
    0x0f00 => {
        Name => 'DataDump',
        Writable => 0,
        Binary => 1,
    },
    0x0f01 => { #6
        Name => 'DataDump2',
        Writable => 0,
        Binary => 1,
    },
    0x0f04 => {
        Name => 'ZoomedPreviewStart',
        # NOTE: this tag is currently not updated properly when the image is rewritten!
        OffsetPair => 0xf05,
        DataTag => 'ZoomedPreviewImage',
        Writable => 'int32u',
        Protected => 2,
    },
    0x0f05 => {
        Name => 'ZoomedPreviewLength',
        OffsetPair => 0xf04,
        DataTag => 'ZoomedPreviewImage',
        Writable => 'int32u',
        Protected => 2,
    },
    0x0f06 => {
        Name => 'ZoomedPreviewSize',
        Writable => 'int16u',
        Count => 2,
    },
    0x1000 => { #6
        Name => 'ShutterSpeedValue',
        Writable => 'rational64s',
        Priority => 0,
        ValueConv => 'abs($val)<100 ? 2**(-$val) : 0',
        ValueConvInv => '$val>0 ? -log($val)/log(2) : -100',
        PrintConv => 'Image::ExifTool::Exif::PrintExposureTime($val)',
        PrintConvInv => 'Image::ExifTool::Exif::ConvertFraction($val)',
    },
    0x1001 => { #6
        Name => 'ISOValue',
        Writable => 'rational64s',
        Priority => 0,
        ValueConv => '100 * 2 ** ($val - 5)',
        ValueConvInv => '$val>0 ? log($val/100)/log(2)+5 : 0',
        PrintConv => 'int($val * 100 + 0.5) / 100',
        PrintConvInv => '$val',
    },
    0x1002 => { #6
        Name => 'ApertureValue',
        Writable => 'rational64s',
        Priority => 0,
        ValueConv => '2 ** ($val / 2)',
        ValueConvInv => '$val>0 ? 2*log($val)/log(2) : 0',
        PrintConv => 'sprintf("%.1f",$val)',
        PrintConvInv => '$val',
    },
    0x1003 => { #6
        Name => 'BrightnessValue',
        Writable => 'rational64s',
        Priority => 0,
    },
    0x1004 => { #3
        Name => 'FlashMode',
        Writable => 'int16u',
        PrintConv => {
            2 => 'On', #PH
            3 => 'Off', #PH
        },
    },
    0x1005 => { #6
        Name => 'FlashDevice',
        Writable => 'int16u',
        PrintConv => {
            0 => 'None',
            1 => 'Internal',
            4 => 'External',
            5 => 'Internal + External',
        },
    },
    0x1006 => { #6
        Name =>'ExposureCompensation',
        Writable => 'rational64s',
    },
    0x1007 => { Name => 'SensorTemperature',Writable => 'int16s' }, #6 (E-10, E-20 and C2500L - numbers usually around 30-40)
    0x1008 => { Name => 'LensTemperature',  Writable => 'int16s' }, #6
    0x1009 => { Name => 'LightCondition',   Writable => 'int16u' }, #11
    0x100a => { #11
        Name => 'FocusRange',
        Writable => 'int16u',
        PrintConv => {
            0 => 'Normal',
            1 => 'Macro',
        },
    },
    0x100b => { #6
        Name => 'FocusMode',
        Writable => 'int16u',
        PrintConv => {
            0 => 'Auto',
            1 => 'Manual',
        },
    },
    0x100c => { #6
        Name => 'ManualFocusDistance',
        Writable => 'rational64u',
        PrintConv => '"$val mm"', #11
        PrintConvInv => '$val=~s/\s*mm$//; $val',
    },
    0x100d => { Name => 'ZoomStepCount',    Writable => 'int16u' }, #6
    0x100e => { Name => 'FocusStepCount',   Writable => 'int16u' }, #6
    0x100f => { #6
        Name => 'Sharpness',
        Writable => 'int16u',
        Priority => 0,
        PrintConv => {
            0 => 'Normal',
            1 => 'Hard',
            2 => 'Soft',
        },
    },
    0x1010 => { Name => 'FlashChargeLevel', Writable => 'int16u' }, #6
    0x1011 => { #3
        Name => 'ColorMatrix',
        Writable => 'int16u',
        Format => 'int16s',
        Count => 9,
    },
    0x1012 => { Name => 'BlackLevel',       Writable => 'int16u', Count => 4 }, #3
    0x1013 => { #11
        Name => 'ColorTemperatureBG',
        Writable => 'int16u',
        Unknown => 1, # (doesn't look like a temperature)
    },
    0x1014 => { #11
        Name => 'ColorTemperatureRG',
        Writable => 'int16u',
        Unknown => 1, # (doesn't look like a temperature)
    },
    0x1015 => { #6
        Name => 'WBMode',
        Writable => 'int16u',
        Count => 2,
        PrintConvColumns => 2,
        PrintConv => {
            '1'   => 'Auto',
            '1 0' => 'Auto',
            '1 2' => 'Auto (2)',
            '1 4' => 'Auto (4)',
            '2 2' => '3000 Kelvin',
            '2 3' => '3700 Kelvin',
            '2 4' => '4000 Kelvin',
            '2 5' => '4500 Kelvin',
            '2 6' => '5500 Kelvin',
            '2 7' => '6500 Kelvin',
            '2 8' => '7500 Kelvin',
            '3 0' => 'One-touch',
        },
    },
    0x1017 => { #2
        Name => 'RedBalance',
        Writable => 'int16u',
        Count => 2,
        ValueConv => '$val=~s/ .*//; $val / 256',
        ValueConvInv => '$val*=256;"$val 64"',
    },
    0x1018 => { #2
        Name => 'BlueBalance',
        Writable => 'int16u',
        Count => 2,
        ValueConv => '$val=~s/ .*//; $val / 256',
        ValueConvInv => '$val*=256;"$val 64"',
    },
    0x1019 => { Name => 'ColorMatrixNumber',    Writable => 'int16u' }, #11
    # 0x101a is same as CameraID ("OLYMPUS DIGITAL CAMERA") for C2500L - PH
    0x101a => { Name => 'SerialNumber',         Writable => 'string' }, #3
    0x101b => { #11
        Name => 'ExternalFlashAE1_0',
        Writable => 'int32u',
        Unknown => 1, # (what are these?)
    },
    0x101c => { Name => 'ExternalFlashAE2_0',   Writable => 'int32u', Unknown => 1 }, #11
    0x101d => { Name => 'InternalFlashAE1_0',   Writable => 'int32u', Unknown => 1 }, #11
    0x101e => { Name => 'InternalFlashAE2_0',   Writable => 'int32u', Unknown => 1 }, #11
    0x101f => { Name => 'ExternalFlashAE1',     Writable => 'int32u', Unknown => 1 }, #11
    0x1020 => { Name => 'ExternalFlashAE2',     Writable => 'int32u', Unknown => 1 }, #11
    0x1021 => { Name => 'InternalFlashAE1',     Writable => 'int32u', Unknown => 1 }, #11
    0x1022 => { Name => 'InternalFlashAE2',     Writable => 'int32u', Unknown => 1 }, #11
    0x1023 => { Name => 'FlashExposureComp',    Writable => 'rational64s' }, #6
    0x1024 => { Name => 'InternalFlashTable',   Writable => 'int16u' }, #11
    0x1025 => { Name => 'ExternalFlashGValue',  Writable => 'rational64s' }, #11
    0x1026 => { #6
        Name => 'ExternalFlashBounce',
        Writable => 'int16u',
        PrintConv => {
            0 => 'No',
            1 => 'Yes',
        },
    },
    0x1027 => { Name => 'ExternalFlashZoom',    Writable => 'int16u' }, #6
    0x1028 => { Name => 'ExternalFlashMode',    Writable => 'int16u' }, #6
    0x1029 => { #3
        Name => 'Contrast',
        Writable => 'int16u',
        PrintConv => { #PH (works with E1)
            0 => 'High',
            1 => 'Normal',
            2 => 'Low',
        },
    },
    0x102a => { Name => 'SharpnessFactor',      Writable => 'int16u' }, #3
    0x102b => { Name => 'ColorControl',         Writable => 'int16u', Count => 6 }, #3
    0x102c => { Name => 'ValidBits',            Writable => 'int16u', Count => 2 }, #3
    0x102d => { Name => 'CoringFilter',         Writable => 'int16u' }, #3
    0x102e => { Name => 'OlympusImageWidth',    Writable => 'int32u' }, #PH
    0x102f => { Name => 'OlympusImageHeight',   Writable => 'int32u' }, #PH
    0x1030 => { Name => 'SceneDetect',          Writable => 'int16u' }, #11
    0x1031 => { #11
        Name => 'SceneArea',
        Writable => 'int32u',
        Count => 8,
        Unknown => 1, # (numbers don't make much sense?)
    },
    # 0x1032 HAFFINAL? #11
    0x1033 => { #11
        Name => 'SceneDetectData',
        Writable => 'int32u',
        Count => 720,
        Binary => 1,
        Unknown => 1, # (but what does it mean?)
    },
    0x1034 => { Name => 'CompressionRatio',    Writable => 'rational64u' }, #3
    0x1035 => { #6
        Name => 'PreviewImageValid',
        Writable => 'int32u',
        PrintConv => { 0 => 'No', 1 => 'Yes' },
    },
    0x1036 => { #6
        Name => 'PreviewImageStart',
        Flags => 'IsOffset',
        OffsetPair => 0x1037, # point to associated byte count
        DataTag => 'PreviewImage',
        Writable => 'int32u',
        WriteGroup => 'MakerNotes',
        Protected => 2,
    },
    0x1037 => { #6
        # (may contain data from multiple previews - PH, FE320)
        Name => 'PreviewImageLength',
        OffsetPair => 0x1036, # point to associated offset
        DataTag => 'PreviewImage',
        Writable => 'int32u',
        WriteGroup => 'MakerNotes',
        Protected => 2,
    },
    0x1038 => { Name => 'AFResult',             Writable => 'int16u' }, #11
    0x1039 => { #6
        Name => 'CCDScanMode',
        Writable => 'int16u',
        PrintConv => {
            0 => 'Interlaced',
            1 => 'Progressive',
        },
    },
    0x103a => { #6
        Name => 'NoiseReduction',
        Writable => 'int16u',
        PrintConv => \%offOn,
    },
    0x103b => { Name => 'FocusStepInfinity',    Writable => 'int16u' }, #6
    0x103c => { Name => 'FocusStepNear',        Writable => 'int16u' }, #6
    0x103d => { Name => 'LightValueCenter',     Writable => 'rational64s' }, #11
    0x103e => { Name => 'LightValuePeriphery',  Writable => 'rational64s' }, #11
    0x103f => { #11
        Name => 'FieldCount',
        Writable => 'int16u',
        Unknown => 1, # (but what does it mean?)
    },
#
# Olympus really screwed up the format of the following subdirectories (for the
# E-1 and E-300 anyway). Not only is the subdirectory value data not included in
# the size, but also the count is 2 bytes short for the subdirectory itself
# (presumably the Olympus programmers forgot about the 2-byte entry count at the
# start of the subdirectory).  This mess is straightened out and these subdirs
# are written properly when ExifTool rewrites the file.  Note that this problem
# has been fixed by Olympus in the new-style IFD maker notes since a standard
# SubIFD offset value is used.  As written by the camera, the old style
# directories have format 'undef' or 'string', and the new style has format
# 'ifd'.  However, some older versions of exiftool may have rewritten the new
# style as 'int32u', so handle both cases. - PH
#
    0x2010 => [ #PH
        {
            Name => 'Equipment',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2, # (so HtmlDump doesn't show these as double-referenced)
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::Equipment',
                ByteOrder => 'Unknown',
            },
        },
        {
            Name => 'EquipmentIFD',
            Groups => { 1 => 'MakerNotes' },    # SubIFD needs group 1 set
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::Equipment',
                Start => '$val',
            },
        },
    ],
    0x2020 => [ #PH
        {
            Name => 'CameraSettings',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::CameraSettings',
                ByteOrder => 'Unknown',
            },
        },
        {
            Name => 'CameraSettingsIFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::CameraSettings',
                Start => '$val',
            },
        },
    ],
    0x2030 => [ #PH
        {
            Name => 'RawDevelopment',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::RawDevelopment',
                ByteOrder => 'Unknown',
            },
        },
        {
            Name => 'RawDevelopmentIFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::RawDevelopment',
                Start => '$val',
            },
        },
    ],
    0x2031 => [ #11
        {
            Name => 'RawDev2',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::RawDevelopment2',
                ByteOrder => 'Unknown',
            },
        },
        {
            Name => 'RawDev2IFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::RawDevelopment2',
                Start => '$val',
            },
        },
    ],
    0x2040 => [ #PH
        {
            Name => 'ImageProcessing',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::ImageProcessing',
                ByteOrder => 'Unknown',
            },
        },
        {
            Name => 'ImageProcessingIFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::ImageProcessing',
                Start => '$val',
            },
        },
    ],
    0x2050 => [ #PH
        {
            Name => 'FocusInfo',
            Condition => '$format ne "ifd" and $format ne "int32u" and not $$self{OlympusCAMER}',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FocusInfo',
                ByteOrder => 'Unknown',
            },
        },
        {
            Name => 'FocusInfoIFD',
            Condition => 'not $$self{OlympusCAMER}',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FocusInfo',
                Start => '$val',
            },
        },
        {
            # ASCII-based camera parameters if makernotes starts with "CAMER\0"
            # (or for Sony models starting with "SONY PI\0" or "PREMI\0")
            Name => 'CameraParameters',
            Writable => 'undef',
            Binary => 1,
        },
    ],
    0x2100 => [
        { #11
            Name => 'Olympus2100',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
            },
        },
        { #PH
            Name => 'Olympus2100IFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
                Start => '$val',
            },
        },
    ],
    0x2200 => [
        { #11
            Name => 'Olympus2200',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
            },
        },
        { #PH
            Name => 'Olympus2200IFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
                Start => '$val',
            },
        },
    ],
    0x2300 => [
        { #11
            Name => 'Olympus2300',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
            },
        },
        { #PH
            Name => 'Olympus2300IFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
                Start => '$val',
            },
        },
    ],
    0x2400 => [
        { #11
            Name => 'Olympus2400',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
            },
        },
        { #PH
            Name => 'Olympus2400IFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
                Start => '$val',
            },
        },
    ],
    0x2500 => [
        { #11
            Name => 'Olympus2500',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
            },
        },
        { #PH
            Name => 'Olympus2500IFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
                Start => '$val',
            },
        },
    ],
    0x2600 => [
        { #11
            Name => 'Olympus2600',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
            },
        },
        { #PH
            Name => 'Olympus2600IFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
                Start => '$val',
            },
        },
    ],
    0x2700 => [
        { #11
            Name => 'Olympus2700',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
            },
        },
        { #PH
            Name => 'Olympus2700IFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
                Start => '$val',
            },
        },
    ],
    0x2800 => [
        { #11
            Name => 'Olympus2800',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
            },
        },
        { #PH
            Name => 'Olympus2800IFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
                Start => '$val',
            },
        },
    ],
    0x2900 => [
        { #11
            Name => 'Olympus2900',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
            },
        },
        { #PH
            Name => 'Olympus2900IFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::FETags',
                ByteOrder => 'Unknown',
                Start => '$val',
            },
        },
    ],
    0x3000 => [
        { #6
            Name => 'RawInfo',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::RawInfo',
                ByteOrder => 'Unknown',
            },
        },
        { #PH
            Name => 'RawInfoIFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::RawInfo',
                Start => '$val',
            },
        },
    ],
    0x4000 => [ #PH
        {
            Name => 'MainInfo',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::Main',
                ByteOrder => 'Unknown',
            },
        },
        {
            Name => 'MainInfoIFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::Main',
                Start => '$val',
            },
        },
    ],
    0x5000 => [ #PH
        {
            Name => 'UnknownInfo',
            Condition => '$format ne "ifd" and $format ne "int32u"',
            NestedHtmlDump => 2,
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::UnknownInfo',
                ByteOrder => 'Unknown',
            },
        },
        {
            Name => 'UnknownInfoIFD',
            Groups => { 1 => 'MakerNotes' },
            Flags => 'SubIFD',
            FixFormat => 'ifd',
            SubDirectory => {
                TagTable => 'Image::ExifTool::Olympus::UnknownInfo',
                Start => '$val',
            },
        },
    ],
);

# TextInfo tags
%Image::ExifTool::Olympus::TextInfo = (
    PROCESS_PROC => \&Image::ExifTool::APP12::ProcessAPP12,
    NOTES => q{
        This information is in text format (similar to APP12 information, but with
        spaces instead of linefeeds).  Below are tags which have been observed, but
        any information found here will be extracted, even if the tag is not listed.
    },
    GROUPS => { 0 => 'MakerNotes', 1 => 'Olympus', 2 => 'Image' },
    Resolution => { },
    Type => {
        Name => 'CameraType',
        Groups => { 2 => 'Camera' },
        DataMember => 'CameraType',
        RawConv => '$self->{CameraType} = $val',
        SeparateTable => 'CameraType',
        PrintConv => \%olympusCameraTypes,
    },
);

# Olympus Equipment IFD
%Image::ExifTool::Olympus::Equipment = (
    WRITE_PROC => \&Image::ExifTool::Exif::WriteExif,
    CHECK_PROC => \&Image::ExifTool::Exif::CheckExif,
    WRITABLE => 1,
    GROUPS => { 0 => 'MakerNotes', 2 => 'Camera' },
    0x000 => { #PH
        Name => 'EquipmentVersion',
        Writable => 'undef',
        RawConv => '$val=~s/\0+$//; $val',  # (may be null terminated)
        Count => 4,
    },
    0x100 => { #6
        Name => 'CameraType2',
        Writable => 'string',
        Count => 6,
        SeparateTable => 'CameraType',
        PrintConv => \%olympusCameraTypes,
    },
    0x101 => { #PH
        Name => 'SerialNumber',
        Writable => 'string',
        Count => 32,
        PrintConv => '$val=~s/\s+$//;$val',
        PrintConvInv => 'pack("A31",$val)', # pad with spaces to 31 chars
    },
    0x102 => { #6
        Name => 'InternalSerialNumber',
        Notes => '16 digits: 0-3=model, 4=year, 5-6=month, 8-12=unit number',
        Writable => 'string',
        Count => 32,
    },
    0x103 => { #6
        Name => 'FocalPlaneDiagonal',
        Writable => 'rational64u',
        PrintConv => '"$val mm"',
        PrintConvInv => '$val=~s/\s*mm$//;$val',
    },
    0x104 => { #6
        Name => 'BodyFirmwareVersion',
        Writable => 'int32u',
        PrintConv => '$val=sprintf("%x",$val);$val=~s/(.{3})$/\.$1/;$val',
        PrintConvInv => '$val=sprintf("%.3f",$val);$val=~s/\.//;hex($val)',
    },
    0x201 => { #6
        Name => 'LensType',
        Writable => 'int8u',
        Count => 6,
        Notes => q{
            6 numbers: 0. Make, 1. Unknown, 2. Model, 3. Sub-model, 4-5. Unknown.  Only
            the Make, Model and Sub-model are used to identify the lens type
        },
        SeparateTable => 'LensType',
        # Have seen these values for the unknown numbers:
        # 1: 0
        # 4: 0, 2(Olympus lenses for which I have also seen 0 for this number)
        # 5: 0, 16(new Lumix lenses)
        ValueConv => 'my @a=split(" ",$val); sprintf("%x %.2x %.2x",@a[0,2,3])',
        # set unknown values to zero when writing
        ValueConvInv => 'my @a=split(" ",$val); hex($a[0])." 0 ".hex($a[1])." ".hex($a[2])." 0 0"',
        PrintConv => \%olympusLensTypes,
    },
    # apparently the first 3 digits of the lens s/n give the type (ref 4):
    # 010 = 50macro
    # 040 = EC-14
    # 050 = 14-54
    # 060 = 50-200
    # 080 = EX-25
    # 101 = FL-50
    # 272 = EC-20 #7
    0x202 => { #PH
        Name => 'LensSerialNumber',
        Writable => 'string',
        Count => 32,
        PrintConv => '$val=~s/\s+$//;$val',
        PrintConvInv => 'pack("A31",$val)', # pad with spaces to 31 chars
    },
    0x203 => { Name => 'LensModel',         Writable => 'string' }, #17
    0x204 => { #6
        Name => 'LensFirmwareVersion',
        Writable => 'int32u',
        PrintConv => '$val=sprintf("%x",$val);$val=~s/(.{3})$/\.$1/;$val',
        PrintConvInv => '$val=sprintf("%.3f",$val);$val=~s/\.//;hex($val)',
    },
    0x205 => { #11
        Name => 'MaxApertureAtMinFocal',
        Writable => 'int16u',
        ValueConv => '$val ? sqrt(2)**($val/256) : 0',
        ValueConvInv => '$val>0 ? int(512*log($val)/log(2)+0.5) : 0',
        PrintConv => 'sprintf("%.1f",$val)',
        PrintConvInv => '$val',
    },
    0x206 => { #5
        Name => 'MaxApertureAtMaxFocal',
        Writable => 'int16u',
        ValueConv => '$val ? sqrt(2)**($val/256) : 0',
        ValueConvInv => '$val>0 ? int(512*log($val)/log(2)+0.5) : 0',
        PrintConv => 'sprintf("%.1f",$val)',
        PrintConvInv => '$val',
    },
    0x207 => { Name => 'MinFocalLength',    Writable => 'int16u' }, #PH
    0x208 => { Name => 'MaxFocalLength',    Writable => 'int16u' }, #PH
    0x20a => { #9
        Name => 'MaxAperture', # (at current focal length)
        Writable => 'int16u',
        ValueConv => '$val ? sqrt(2)**($val/256) : 0',
        ValueConvInv => '$val>0 ? int(512*log($val)/log(2)+0.5) : 0',
        PrintConv => 'sprintf("%.1f",$val)',
        PrintConvInv => '$val',
    },
    0x20b => { #11
        Name => 'LensProperties',
        Writable => 'int16u',
        PrintConv => 'sprintf("0x%x",$val)',
        PrintConvInv => '$val',
    },
    0x301 => { #6
        Name => 'Extender',
        Writable => 'int8u',
        Count => 6,
        Notes => q{
            6 numbers: 0. Make, 1. Unknown, 2. Model, 3. Sub-model, 4-5. Unknown.  Only
            the Make and Model are used to identify the extender
        },
        ValueConv => 'my @a=split(" ",$val); sprintf("%x %.2x",@a[0,2])',
        ValueConvInv => 'my @a=split(" ",$val); hex($a[0])." 0 ".hex($a[1])." 0 0 0"',
        PrintConv => {
            '0 00' => 'None',
            '0 04' => 'Olympus Zuiko Digital EC-14 1.4x Teleconverter',
            '0 08' => 'Olympus EX-25 Extension Tube',
            '0 10' => 'Olympus Zuiko Digital EC-20 2.0x Teleconverter', #7
        },
    },
    0x302 => { Name => 'ExtenderSerialNumber',  Writable => 'string', Count => 32 }, #4
    0x303 => { Name => 'ExtenderModel',         Writable => 'string' }, #9
    0x304 => { #6
        Name => 'ExtenderFirmwareVersion',
        Writable => 'int32u',
        PrintConv => '$val=sprintf("%x",$val);$val=~s/(.{3})$/\.$1/;$val',
        PrintConvInv => '$val=sprintf("%.3f",$val);$val=~s/\.//;hex($val)',
    },
    0x403 => { #http://dev.exiv2.org/issues/870
        Name => 'ConversionLens',
        Writable => 'string',
        # (observed values: '','TCON','FCON','WCON')
    },
    0x1000 => { #6
        Name => 'FlashType',
        Writable => 'int16u',
        PrintConv => {
            0 => 'None',
            2 => 'Simple E-System',
            3 => 'E-System',
        },
    },
    0x1001 => { #6
        Name => 'FlashModel',
        Writable => 'int16u',
        PrintConvColumns => 2,
        PrintConv => {
            0 => 'None',
            1 => 'FL-20', # (or subtronic digital or Inon UW flash, ref 11)
            2 => 'FL-50', # (or Metzblitz+SCA or Cullmann 34, ref 11)
            3 => 'RF-11',
            4 => 'TF-22',
            5 => 'FL-36',
            6 => 'FL-50R', #11 (or Metz mecablitz digital)
            7 => 'FL-36R', #11
            9 => 'FL-14', #11
            11 => 'FL-600R', #11
        },
    },
    0x1002 => { #6
        Name => 'FlashFirmwareVersion',
        Writable => 'int32u',
        PrintConv => '$val=sprintf("%x",$val);$val=~s/(.{3})$/\.$1/;$val',
        PrintConvInv => '$val=sprintf("%.3f",$val);$val=~s/\.//;hex($val)',
    },
    0x1003 => { Name => 'FlashSerialNumber', Writable => 'string', Count => 32 }, #4
);

# Olympus camera settings IFD
%Image::ExifTool::Olympus::CameraSettings = (
    WRITE_PROC => \&Image::ExifTool::Exif::WriteExif,
    CHECK_PROC => \&Image::ExifTool::Exif::CheckExif,
    WRITABLE => 1,
    GROUPS => { 0 => 'MakerNotes', 2 => 'Camera' },
    0x000 => { #PH
        Name => 'CameraSettingsVersion',
        Writable => 'undef',
        RawConv => '$val=~s/\0+$//; $val',  # (may be null terminated)
        Count => 4,
    },
    0x100 => { #6
        Name => 'PreviewImageValid',
        Writable => 'int32u',
        PrintConv => { 0 => 'No', 1 => 'Yes' },
    },
    0x101 => { #PH
        Name => 'PreviewImageStart',
        Flags => 'IsOffset',
        OffsetPair => 0x102,
        DataTag => 'PreviewImage',
        Writable => 'int32u',
        WriteGroup => 'MakerNotes',
        Protected => 2,
    },
    0x102 => { #PH
        Name => 'PreviewImageLength',
        OffsetPair => 0x101,
        DataTag => 'PreviewImage',
        Writable => 'int32u',
        WriteGroup => 'MakerNotes',
        Protected => 2,
    },
    0x200 => { #4
        Name => 'ExposureMode',
        Writable => 'int16u',
        PrintConv => {
            1 => 'Manual',
            2 => 'Program', #6
            3 => 'Aperture-priority AE',
            4 => 'Shutter speed priority AE',
            5 => 'Program-shift', #6
        }
    },
    0x201 => { #6
        Name => 'AELock',
        Writable => 'int16u',
        PrintConv => \%offOn,
    },
    0x202 => { #PH/4
        Name => 'MeteringMode',
        Writable => 'int16u',
        PrintConv => {
            2 => 'Center-weighted average',
            3 => 'Spot',
            5 => 'ESP',
            261 => 'Pattern+AF', #6
            515 => 'Spot+Highlight control', #6
            1027 => 'Spot+Shadow control', #6
        },
    },
    0x203 => { Name => 'ExposureShift', Writable => 'rational64s' }, #11 (some models only)
    0x204 => { #11 (XZ-1)
        Name => 'NDFilter',
        PrintConv => \%offOn,
    },
    0x300 => { #6
        Name => 'MacroMode',
        Writable => 'int16u',
        PrintConv => {
            0 => 'Off',
            1 => 'On',
            2 => 'Super Macro', #11
        },
    },
    0x301 => { #6
        Name => 'FocusMode',
        Writable => 'int16u',
        Count => -1,
        Notes => '1 or 2 values',
        PrintConv => [{
            0 => 'Single AF',
            1 => 'Sequential shooting AF',
            2 => 'Continuous AF',
            3 => 'Multi AF',
            4 => 'Face detect', #11
            10 => 'MF',
        }, {
            0 => '(none)',
            BITMASK => { #11
                0 => 'S-AF',
                2 => 'C-AF',
                4 => 'MF',
                5 => 'Face detect',
                6 => 'Imager AF',
                7 => 'Live View Magnification Frame',
                8 => 'AF sensor',
            },
        }],
    },
    0x302 => { #6
        Name => 'FocusProcess',
        Writable => 'int16u',
        Count => -1,
        Notes => '1 or 2 values',
        PrintConv => [{
            0 => 'AF Not Used',
            1 => 'AF Used',
        }],
        # 2nd value written only by some models (u1050SW, u9000, uT6000, uT6010,
        # uT8000, E-30, E-420, E-450, E-520, E-620, E-P1 and E-P2): - PH
        # observed values when "AF Not Used": 0, 16
        # observed values when "AF Used": 64, 96(face detect on), 256
    },
    0x303 => { #6
        Name => 'AFSearch',
        Writable => 'int16u',
        PrintConv => {
            0 => 'Not Ready',
            1 => 'Ready',
        },
    },
    0x304 => { #PH/4
        Name => 'AFAreas',
        Notes => 'coordinates range from 0 to 255',
        Writable => 'int32u',
        Count => 64,
        PrintConv => 'Image::ExifTool::Olympus::PrintAFAreas($val)',
    },
    0x0305 => { #PH
        Name => 'AFPointSelected',
        Notes => 'coordinates expressed as a percent',
        Writable => 'rational64s',
        Count => 5,
        ValueConv => '$val =~ s/\S* //; $val', # ignore first undefined value
        ValueConvInv => '"undef $val"',
        PrintConv => q{
            return 'n/a' if $val =~ /undef/;
            sprintf("(%d%%,%d%%) (%d%%,%d%%)", map {$_ * 100} split(" ",$val));
        },
        PrintConvInv => q{
            return 'undef undef undef undef' if $val eq 'n/a';
            my @nums = $val =~ /\d+(?:\.\d+)?/g;
            return undef unless @nums == 4;
            join ' ', map {$_ / 100} @nums;
        },
    },
    0x306 => { #11
        Name => 'AFFineTune',
        Writable => 'int8u',
        PrintConv => { 0 => 'Off', 1 => 'On' },
    },
    0x307 => { #15
        Name => 'AFFineTuneAdj',
        Format => 'int16s',
        Count => 3, # not sure what the 3 values mean
    },
    0x400 => { #6
        Name => 'FlashMode',
        Writable => 'int16u',
        PrintConv => {
            0 => 'Off',
            BITMASK => {
                0 => 'On',
                1 => 'Fill-in',
                2 => 'Red-eye',
                3 => 'Slow-sync',
                4 => 'Forced On',
                5 => '2nd Curtain',
            },
        },
    },
    0x401 => { Name => 'FlashExposureComp', Writable => 'rational64s' }, #6
    # 0x402 - FlashMode? bit0=TTL, bit1=auto, bit2=SuperFP (ref 11)
    0x403 => { #11
        Name => 'FlashRemoteControl',
        Writable => 'int16u',
        PrintHex => 1,
        PrintConvColumns => 2,
        PrintConv => {
            0 => 'Off',
            0x01 => 'Channel 1, Low',
            0x02 => 'Channel 2, Low',
            0x03 => 'Channel 3, Low',
            0x04 => 'Channel 4, Low',
            0x09 => 'Channel 1, Mid',
            0x0a => 'Channel 2, Mid',
            0x0b => 'Channel 3, Mid',
            0x0c => 'Channel 4, Mid',
            0x11 => 'Channel 1, High',
            0x12 => 'Channel 2, High',
            0x13 => 'Channel 3, High',
            0x14 => 'Channel 4, High',
        },
    },
    0x404 => { #11
        Name => 'FlashControlMode',
        Writable => 'int16u',
        Count => -1,
        Notes => '3 or 4 values',
        PrintConv => [{
            0 => 'Off',
            3 => 'TTL',
            4 => 'Auto',
            5 => 'Manual',
        }],
    },
    0x405 => { #11
        Name => 'FlashIntensity',
        Writable => 'rational64s',
        Count => -1,
        Notes => '3 or 4 values',
        PrintConv => {
            OTHER => sub { shift },
            'undef undef undef' => 'n/a',
            'undef undef undef undef' => 'n/a (x4)',
        },
    },
    0x406 => { #11
        Name => 'ManualFlashStrength',
        Writable => 'rational64s',
        Count => -1,
        Notes => '3 or 4 values',
        PrintConv => {
            OTHER => sub { shift },
            'undef undef undef' => 'n/a',
            'undef undef undef undef' => 'n/a (x4)',
        },
    },
    0x500 => { #6
        Name => 'WhiteBalance2',
        Writable => 'int16u',
        PrintConv => {
            0 => 'Auto',
            1 => 'Auto (Keep Warm Color Off)', #IB
            16 => '7500K (Fine Weather with Shade)',
            17 => '6000K (Cloudy)',
            18 => '5300K (Fine Weather)',
            20 => '3000K (Tungsten light)',
            21 => '3600K (Tungsten light-like)',
            22 => 'Auto Setup', #IB
            23 => '5500K (Flash)', #IB
            33 => '6600K (Daylight fluorescent)',
            34 => '4500K (Neutral white fluorescent)',
            35 => '4000K (Cool white fluorescent)',
            36 => 'White Fluorescent', #IB
            48 => '3600K (Tungsten light-like)',
            67 => 'Underwater', #IB
            256 => 'One Touch WB 1', #IB
            257 => 'One Touch WB 2', #IB
            258 => 'One Touch WB 3', #IB
            259 => 'One Touch WB 4', #IB
            512 => 'Custom WB 1', #IB
            513 => 'Custom WB 2', #IB
            514 => 'Custom WB 3', #IB
            515 => 'Custom WB 4', #IB
        },
    },
    0x501 => { #PH/4
        Name => 'WhiteBalanceTemperature',
        Writable => 'int16u',
        PrintConv => '$val ? $val : "Auto"',
        PrintConvInv => '$val=~/^\d+$/ ? $val : 0',
    },
    0x502 => {  #PH/4
        Name => 'WhiteBalanceBracket',
        Writable => 'int16s',
    },
    0x503 => { #PH/4/6
        Name => 'CustomSaturation',
        Writable => 'int16s',
        Count => 3,
        Notes => '3 numbers: 1. CS Value, 2. Min, 3. Max',
        PrintConv => q{
            my ($a,$b,$c)=split ' ',$val;
            if ($self->{Model} =~ /^E-1\b/) {
                $a-=$b; $c-=$b;
                return "CS$a (min CS0, max CS$c)";
            } else {
                return "$a (min $b, max $c)";
            }
        },
    },
    0x504 => { #PH/4
        Name => 'ModifiedSaturation',
        Writable => 'int16u',
        PrintConv => {
            0 => 'Off',
            1 => 'CM1 (Red Enhance)',
            2 => 'CM2 (Green Enhance)',
            3 => 'CM3 (Blue Enhance)',
            4 => 'CM4 (Skin Tones)',
        },
    },
    0x505 => { #PH/4
        Name => 'ContrastSetting',
        Writable => 'int16s',
        Count => 3,
        Notes => 'value, min, max',
        PrintConv => 'my @v=split " ",$val; "$v[0] (min $v[1], max $v[2])"',
        PrintConvInv => '$val=~tr/-0-9 //dc;$val',
    },
    0x506 => { #PH/4
        Name => 'SharpnessSetting',
        Writable => 'int16s',
        Count => 3,
        Notes => 'value, min, max',
        PrintConv => 'my @v=split " ",$val; "$v[0] (min $v[1], max $v[2])"',
        PrintConvInv => '$val=~tr/-0-9 //dc;$val',
    },
    0x507 => { #PH/4
        Name => 'ColorSpace',
        Writable => 'int16u',
        PrintConv => { #6
            0 => 'sRGB',
            1 => 'Adobe RGB',
            2 => 'Pro Photo RGB',
        },
    },
    0x509 => { #6
        Name => 'SceneMode',
        Writable => 'int16u',
        PrintConvColumns => 2,
        PrintConv => {
            0 => 'Standard',
            6 => 'Auto', #6
            7 => 'Sport',
            8 => 'Portrait',
            9 => 'Landscape+Portrait',
            10 => 'Landscape',
            11 => 'Night Scene',
            12 => 'Self Portrait', #11
            13 => 'Panorama', #6
            14 => '2 in 1', #11
            15 => 'Movie', #11
            16 => 'Landscape+Portrait', #6
            17 => 'Night+Portrait',
            18 => 'Indoor', #11 (Party - PH)
            19 => 'Fireworks',
            20 => 'Sunset',
            21 => 'Beauty Skin', #PH
            22 => 'Macro',
            23 => 'Super Macro', #11
            24 => 'Food', #11
            25 => 'Documents',
            26 => 'Museum',
            27 => 'Shoot & Select', #11
            28 => 'Beach & Snow',
            29 => 'Self Protrait+Timer', #11
            30 => 'Candle',
            31 => 'Available Light', #11
            32 => 'Behind Glass', #11
            33 => 'My Mode', #11
            34 => 'Pet', #11
            35 => 'Underwater Wide1', #6
            36 => 'Underwater Macro', #6
            37 => 'Shoot & Select1', #11
            38 => 'Shoot & Select2', #11
            39 => 'High Key',
            40 => 'Digital Image Stabilization', #6
            41 => 'Auction', #11
            42 => 'Beach', #11
            43 => 'Snow', #11
            44 => 'Underwater Wide2', #6
            45 => 'Low Key', #6
            46 => 'Children', #6
            47 => 'Vivid', #11
            48 => 'Nature Macro', #6
            49 => 'Underwater Snapshot', #11
            50 => 'Shooting Guide', #11
            54 => 'Face Portrait', #11
            57 => 'Bulb', #11
            59 => 'Smile Shot', #11
            60 => 'Quick Shutter', #11
            63 => 'Slow Shutter', #11
            64 => 'Bird Watching', #11
            65 => 'Multiple Exposure', #11
            66 => 'e-Portrait', #11
            67 => 'Soft Background Shot', #11
            142 => 'Hand-held Starlight', #PH (SH-21)
            154 => 'HDR', #PH (XZ-2)
        },
    },
    0x50a => { #PH/4/6
        Name => 'NoiseReduction',
        Writable => 'int16u',
        PrintConv => {
            0 => '(none)',
            BITMASK => {
                0 => 'Noise Reduction',
                1 => 'Noise Filter',
                2 => 'Noise Filter (ISO Boost)',
                3 => 'Auto', #11
            },
        },
    },
    0x50b => { #6
        Name => 'DistortionCorrection',
        Writable => 'int16u',
        PrintConv => \%offOn,
    },
    0x50c => { #PH/4
        Name => 'ShadingCompensation',
        Writable => 'int16u',
        PrintConv => \%offOn,
    },
    0x50d => { Name => 'CompressionFactor', Writable => 'rational64u' }, #PH/4
    0x50f => { #6
        Name => 'Gradation',
        Writable => 'int16s',
        Notes => '3 or 4 values',
        Count => -1,
        Relist => [ [0..2], 3 ], # join values 0-2 for PrintConv
        PrintConv => [{
           '0 0 0' => 'n/a', #PH (?)
           '-1 -1 1' => 'Low Key',
            '0 -1 1' => 'Normal',
            '1 -1 1' => 'High Key',
        },{
            0 => 'User-Selected',
            1 => 'Auto-Override',
        }],
    },
    0x520 => { #6
        Name => 'PictureMode',
        Writable => 'int16u',
        Notes => '1 or 2 values',
        Count => -1,
        PrintConv => [{
            1 => 'Vivid',
            2 => 'Natural',
            3 => 'Muted',
            4 => 'Portrait',
            5 => 'i-Enhance', #11
            6 => 'e-Portrait', #23
            7 => 'Color Creator', #23
            9 => 'Color Profile 1', #23
            10 => 'Color Profile 2', #23
            11 => 'Color Profile 3', #23
            12 => 'Monochrome Profile 1', #23
            13 => 'Monochrome Profile 2', #23
            14 => 'Monochrome Profile 3', #23
            256 => 'Monotone',
            512 => 'Sepia',
        }],
    },
    0x521 => { #6
        Name => 'PictureModeSaturation',
        Writable => 'int16s',
        Count => 3,
        Notes => 'value, min, max',
        PrintConv => 'my @v=split " ",$val; "$v[0] (min $v[1], max $v[2])"',
        PrintConvInv => '$val=~tr/-0-9 //dc;$val',
    },
    0x522 => { #6
        Name => 'PictureModeHue',
        Writable => 'int16s',
        Unknown => 1, # (needs verification)
    },
    0x523 => { #6
        Name => 'PictureModeContrast',
        Writable => 'int16s',
        Count => 3,
        Notes => 'value, min, max',
        PrintConv => 'my @v=split " ",$val; "$v[0] (min $v[1], max $v[2])"',
        PrintConvInv => '$val=~tr/-0-9 //dc;$val',
    },
    0x524 => { #6
        Name => 'PictureModeSharpness',
        # verified as the Sharpness setting in the Picture Mode menu for the E-410
        Writable => 'int16s',
        Count => 3,
        Notes => 'value, min, max',
        PrintConv => 'my @v=split " ",$val; "$v[0] (min $v[1], max $v[2])"',
        PrintConvInv => '$val=~tr/-0-9 //dc;$val',
    },
    0x525 => { #6
        Name => 'PictureModeBWFilter',
        Writable => 'int16s',
        PrintConvColumns => 2,
        PrintConv => {
            0 => 'n/a',
            1 => 'Neutral',
            2 => 'Yellow',
            3 => 'Orange',
            4 => 'Red',
            5 => 'Green',
        },
    },
    0x526 => { #6
        Name => 'PictureModeTone',
        Writable => 'int16s',
        PrintConvColumns => 2,
