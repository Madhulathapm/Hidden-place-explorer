@echo off
title Push Hidden Places Explorer to GitHub
echo ==========================================================
echo        PUSH HIDDEN PLACES EXPLORER TO GITHUB
echo ==========================================================
echo.
cd /d "%~dp0"

echo 1. Check current git remote
git remote -v

echo.
set /p REPO_URL="Enter your GitHub Repository URL (or press Enter for default): "

if "%REPO_URL%"=="" (
    echo No URL entered.
    git remote get-url origin >nul 2>&1
    if errorlevel 1 (
        echo Please create a new repository on GitHub: https://github.com/new
        echo Suggested name: hidden-places-explorer
        echo Then run this script again and paste the repository URL.
        echo.
        pause
        exit /b 1
    )
) else (
    git remote remove origin >nul 2>&1
    git remote add origin %REPO_URL%
    echo Remote set to %REPO_URL%
)

echo.
echo Pushing to GitHub (branch: main)...
git branch -M main
git push -u origin main

echo.
if %ERRORLEVEL% EQU 0 (
    echo ==========================================================
    echo SUCCESS: Hidden Places Explorer successfully pushed!
    echo ==========================================================
) else (
    echo ==========================================================
    echo Note: If authentication is required, sign in via browser
    echo or Personal Access Token when prompted.
    echo ==========================================================
)
pause
