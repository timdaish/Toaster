@echo off
if "%OS%" == "Windows_NT" goto WinNT
"C:\Program Files (x86)\SIL\FontUtils\fontutils.exe" ttfremap %1 %2 %3 %4 %5 %6 %7 %8 %9
goto end
:WinNT
"C:\Program Files (x86)\SIL\FontUtils\fontutils.exe" ttfremap %*
:end
