; Hassan IDE Installer Configuration
; This file is used with the standard code.iss template

#define RootLicenseFileName FileExists(RepoDir + '\LICENSE.rtf') ? 'LICENSE.rtf' : 'LICENSE.txt'
#define LocalizedLanguageFile(Language = "") \
    DirExists(RepoDir + "\licenses") && Language != "" \
      ? ('; LicenseFile: "' + RepoDir + '\licenses\LICENSE-' + Language + '.rtf"') \
      : '; LicenseFile: "' + RepoDir + '\' + RootLicenseFileName + '"'

[Setup]
AppId={#AppId}
AppName={#NameLong}
AppVerName={#NameVersion}
AppPublisher=Hassan Tech
AppPublisherURL=https://hassanide.com/
AppSupportURL=https://hassanide.com/support
AppUpdatesURL=https://hassanide.com/download
DefaultGroupName={#NameLong}
AllowNoIcons=yes
OutputDir={#OutputDir}
OutputBaseFilename=HassanIDESetup
Compression=lzma
SolidCompression=yes
AppMutex={code:GetAppMutex}
SetupMutex={#AppMutex}setup
WizardImageFile="{#RepoDir}\resources\win32\inno-big-100.bmp,{#RepoDir}\resources\win32\inno-big-125.bmp,{#RepoDir}\resources\win32\inno-big-150.bmp,{#RepoDir}\resources\win32\inno-big-175.bmp,{#RepoDir}\resources\win32\inno-big-200.bmp,{#RepoDir}\resources\win32\inno-big-225.bmp,{#RepoDir}\resources\win32\inno-big-250.bmp"
WizardSmallImageFile="{#RepoDir}\resources\win32\inno-small-100.bmp,{#RepoDir}\resources\win32\inno-small-125.bmp,{#RepoDir}\resources\win32\inno-small-150.bmp,{#RepoDir}\resources\win32\inno-small-175.bmp,{#RepoDir}\resources\win32\inno-small-200.bmp,{#RepoDir}\resources\win32\inno-small-225.bmp,{#RepoDir}\resources\win32\inno-small-250.bmp"
SetupIconFile={#RepoDir}\resources\win32\hassanide.ico
UninstallDisplayIcon={app}\{#ExeBasename}.exe
ChangesEnvironment=true
ChangesAssociations=true
MinVersion=10.0
SourceDir={#SourceDir}
AppVersion={#Version}
VersionInfoVersion={#RawVersion}
ShowLanguageDialog=auto
ArchitecturesAllowed={#ArchitecturesAllowed}
ArchitecturesInstallIn64BitMode={#ArchitecturesInstallIn64BitMode}
WizardStyle=modern

CloseApplications=force

#ifdef Sign
SignTool=esrp
#endif

#if "user" == InstallTarget
DefaultDirName={userpf}\{#DirName}
PrivilegesRequired=lowest
#else
DefaultDirName={pf}\{#DirName}
#endif

[Languages]
Name: "english"; MessagesFile: "compiler:Default.isl,{#RepoDir}\build\win32\i18n\messages.en.isl" {#LocalizedLanguageFile}
Name: "arabic"; MessagesFile: "{#RepoDir}\build\win32\i18n\Default.ar.isl,{#RepoDir}\build\win32\i18n\messages.ar.isl" {#LocalizedLanguageFile("arb")}
Name: "german"; MessagesFile: "compiler:Languages\German.isl,{#RepoDir}\build\win32\i18n\messages.de.isl" {#LocalizedLanguageFile("deu")}
Name: "spanish"; MessagesFile: "compiler:Languages\Spanish.isl,{#RepoDir}\build\win32\i18n\messages.es.isl" {#LocalizedLanguageFile("esp")}
Name: "french"; MessagesFile: "compiler:Languages\French.isl,{#RepoDir}\build\win32\i18n\messages.fr.isl" {#LocalizedLanguageFile("fra")}
Name: "italian"; MessagesFile: "compiler:Languages\Italian.isl,{#RepoDir}\build\win32\i18n\messages.it.isl" {#LocalizedLanguageFile("ita")}
Name: "japanese"; MessagesFile: "compiler:Languages\Japanese.isl,{#RepoDir}\build\win32\i18n\messages.ja.isl" {#LocalizedLanguageFile("jpn")}
Name: "russian"; MessagesFile: "compiler:Languages\Russian.isl,{#RepoDir}\build\win32\i18n\messages.ru.isl" {#LocalizedLanguageFile("rus")}
Name: "korean"; MessagesFile: "{#RepoDir}\build\win32\i18n\Default.ko.isl,{#RepoDir}\build\win32\i18n\messages.ko.isl" {#LocalizedLanguageFile("kor")}
Name: "simplifiedChinese"; MessagesFile: "{#RepoDir}\build\win32\i18n\Default.zh-cn.isl,{#RepoDir}\build\win32\i18n\messages.zh-cn.isl" {#LocalizedLanguageFile("chs")}
Name: "traditionalChinese"; MessagesFile: "{#RepoDir}\build\win32\i18n\Default.zh-tw.isl,{#RepoDir}\build\win32\i18n\messages.zh-tw.isl" {#LocalizedLanguageFile("cht")}
Name: "brazilianPortuguese"; MessagesFile: "compiler:Languages\BrazilianPortuguese.isl,{#RepoDir}\build\win32\i18n\messages.pt-br.isl" {#LocalizedLanguageFile("ptb")}
Name: "hungarian"; MessagesFile: "{#RepoDir}\build\win32\i18n\Default.hu.isl,{#RepoDir}\build\win32\i18n\messages.hu.isl" {#LocalizedLanguageFile("hun")}
Name: "turkish"; MessagesFile: "compiler:Languages\Turkish.isl,{#RepoDir}\build\win32\i18n\messages.tr.isl" {#LocalizedLanguageFile("trk")}

; Include the rest of the standard installer configuration
#include "code.iss"
