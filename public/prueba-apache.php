<?php
header('Content-Type: text/plain');
header('Set-Cookie: prueba-apache=123456; Path=/; Secure; HttpOnly; SameSite=Lax');
echo 'PRUEBA PHP DIRECTO';