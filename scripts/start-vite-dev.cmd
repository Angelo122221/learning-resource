@echo off
cd /d C:\Users\sampo\learning-system
call "C:\Program Files\nodejs\npm.cmd" run dev -- --host 127.0.0.1 --port 5173 >> storage\logs\vite.console.log 2>&1
