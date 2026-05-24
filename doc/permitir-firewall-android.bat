@echo off
:: Ejecutar clic derecho -> Ejecutar como administrador
echo Permitiendo puerto 80 (HTTP) para red privada - acceso Android...
netsh advfirewall firewall add rule name="AlCorte HTTP 80" dir=in action=allow protocol=TCP localport=80 profile=private
netsh advfirewall firewall add rule name="AlCorte Apache" dir=in action=allow program="C:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18\bin\httpd.exe" enable=yes profile=private
echo.
echo Listo. Prueba en Android: http://TU_IP/alcorte/
pause
