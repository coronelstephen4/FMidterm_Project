Add-Type -AssemblyName System.Drawing
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$ErrorActionPreference = 'Stop'

$root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$outDir = Join-Path $PSScriptRoot 'report_visuals'
if (-not (Test-Path -LiteralPath $outDir)) { New-Item -ItemType Directory -Path $outDir | Out-Null }
$pages = Join-Path $outDir 'pages'
if (-not (Test-Path -LiteralPath $pages)) { New-Item -ItemType Directory -Path $pages | Out-Null }

$script:W = 1600; $script:H = 900
$script:navy = [Drawing.Color]::FromArgb(15,23,42)
$script:panel = [Drawing.Color]::FromArgb(30,41,59)
$script:green = [Drawing.Color]::FromArgb(5,105,78)
$script:mint = [Drawing.Color]::FromArgb(52,211,153)
$script:white = [Drawing.Color]::FromArgb(248,250,252)
$script:muted = [Drawing.Color]::FromArgb(190,204,220)
$script:blue = [Drawing.Color]::FromArgb(96,165,250)
$script:gold = [Drawing.Color]::FromArgb(251,191,36)
$script:red = [Drawing.Color]::FromArgb(251,113,133)
$script:fontTitle = New-Object Drawing.Font('Segoe UI', 36, [Drawing.FontStyle]::Bold)
$script:fontHead = New-Object Drawing.Font('Segoe UI', 23, [Drawing.FontStyle]::Bold)
$script:fontBody = New-Object Drawing.Font('Segoe UI', 17, [Drawing.FontStyle]::Regular)
$script:fontSmall = New-Object Drawing.Font('Segoe UI', 14, [Drawing.FontStyle]::Regular)
$script:fontLabel = New-Object Drawing.Font('Segoe UI', 15, [Drawing.FontStyle]::Bold)

function New-Canvas([string]$title, [string]$subtitle, [string]$tag) {
    $bmp = New-Object Drawing.Bitmap($script:W,$script:H)
    $g = [Drawing.Graphics]::FromImage($bmp); $g.SmoothingMode = 'AntiAlias'; $g.TextRenderingHint = 'ClearTypeGridFit'
    $g.Clear($script:navy)
    $g.FillRectangle((New-Object Drawing.SolidBrush($script:green)),0,0,$script:W,13)
    $g.DrawString($tag,(New-Object Drawing.Font('Segoe UI',13,[Drawing.FontStyle]::Bold)),(New-Object Drawing.SolidBrush($script:mint)),70,42)
    $g.DrawString($title,$script:fontTitle,(New-Object Drawing.SolidBrush($script:white)),70,82)
    $g.DrawString($subtitle,$script:fontBody,(New-Object Drawing.SolidBrush($script:muted)),72,142)
    return ,@($bmp,$g)
}
function Add-Card($g,[int]$x,[int]$y,[int]$w,[int]$h,[string]$title,[string]$body,[Drawing.Color]$accent=$script:mint) {
    $body = $body.Replace(([char]96 + 'n'),[Environment]::NewLine)
    $g.FillRectangle((New-Object Drawing.SolidBrush($script:panel)),$x,$y,$w,$h)
    $g.FillRectangle((New-Object Drawing.SolidBrush($accent)),$x,$y,7,$h)
    $g.DrawString($title,$script:fontHead,(New-Object Drawing.SolidBrush($script:white)),$x+24,$y+18)
    $rect = [Drawing.RectangleF]::new(($x+26),($y+61),($w-48),($h-74))
    $g.DrawString($body,$script:fontBody,(New-Object Drawing.SolidBrush($script:muted)),$rect)
}
function Add-Node($g,[int]$x,[int]$y,[int]$w,[int]$h,[string]$title,[string]$fields,[Drawing.Color]$accent=$script:mint) {
    $fields = $fields.Replace(([char]96 + 'n'),[Environment]::NewLine)
    $g.FillRectangle((New-Object Drawing.SolidBrush($script:panel)),$x,$y,$w,$h)
    $g.DrawRectangle((New-Object Drawing.Pen($accent,3)),$x,$y,$w,$h)
    $g.DrawString($title,$script:fontLabel,(New-Object Drawing.SolidBrush($script:white)),$x+14,$y+10)
    $g.DrawString($fields,$script:fontSmall,(New-Object Drawing.SolidBrush($script:muted)),[Drawing.RectangleF]::new(($x+14),($y+40),($w-25),($h-45)))
}
function Save-Page($obj,[string]$name) {
    $bmp=$obj[0]; $g=$obj[1]
    $g.DrawString('AGRICULTURE INFORMATION SYSTEM  |  AP3 MIDTERM PROJECT',(New-Object Drawing.Font('Segoe UI',12,[Drawing.FontStyle]::Bold)),(New-Object Drawing.SolidBrush($script:muted)),70,855)
    $g.DrawString('12 NOV 2026',(New-Object Drawing.Font('Segoe UI',12,[Drawing.FontStyle]::Bold)),(New-Object Drawing.SolidBrush($script:muted)),1390,855)
    $bmp.Save((Join-Path $pages $name),[Drawing.Imaging.ImageFormat]::Png); $g.Dispose(); $bmp.Dispose()
}

# Cover
$p=New-Canvas 'AGRICULTURE' 'INFORMATION SYSTEM' 'AP3  /  FINAL PROJECT REPORT'
$g=$p[1]
$g.FillRectangle((New-Object Drawing.SolidBrush($script:green)),70,235,1460,390)
$g.DrawString('A visual guide to the system, data model, and project readiness',$script:fontHead,(New-Object Drawing.SolidBrush($script:white)),120,290)
$g.DrawString('Cebu Technological University  |  Native PHP + PDO  |  Supabase PostgreSQL',$script:fontBody,(New-Object Drawing.SolidBrush($script:white)),122,350)
Add-Card $g 120 430 410 145 '5 core modules' 'Farmers  |  Farms  |  Crops`nPlantings  |  Harvests' $script:gold
Add-Card $g 595 430 410 145 '3NF data model' '5 business tables + 7 lookups`n+ users table' $script:mint
Add-Card $g 1070 430 410 145 'Evidence status' 'Screenshots and final data checks`nremain to be completed' $script:blue
Save-Page $p '01_cover.png'

# System overview
$p=New-Canvas 'System at a glance' 'A web application connected to a relational database.' '01  /  ARCHITECTURE'
$g=$p[1]
Add-Card $g 70 230 330 205 'People' 'Administrator: full access`nStaff: view only' $script:gold
Add-Card $g 465 230 330 205 'Browser interface' 'Responsive pages`nSearch | filters | forms`nDashboard | reports' $script:blue
Add-Card $g 860 230 300 205 'PHP application' 'Native PHP OOP`nPDO prepared statements`nServer-side validation' $script:mint
Add-Card $g 1225 230 305 205 'Supabase' 'PostgreSQL`nForeign keys | checks`nLookup-backed values' $script:gold
foreach($x in @(405,800,1165)){ $g.DrawLine((New-Object Drawing.Pen($script:mint,6)),$x,330,$x+48,330); $g.FillPolygon((New-Object Drawing.SolidBrush($script:mint)),[Drawing.Point[]]@([Drawing.Point]::new($x+48,320),[Drawing.Point]::new($x+66,330),[Drawing.Point]::new($x+48,340))) }
Add-Card $g 70 500 1460 230 'Design choices' 'Passwords are stored as hashes. SQL uses prepared statements. Related labels are supplied by lookup tables and shown through joins. Friendly application messages are used for expected errors.' $script:mint
Save-Page $p '02_architecture.png'

# Modules and workflow
$p=New-Canvas 'How records connect' 'The five project modules follow the farmer-to-harvest workflow.' '02  /  MODULES'
$g=$p[1]
$items=@(@('FARMER','Person and contact'),@('FARM','Owned by farmer'),@('CROP','Crop and variety'),@('PLANTING','Crop planted on farm'),@('HARVEST','Yield from planting'))
$x=70
foreach($it in $items){ Add-Node $g $x 290 270 170 $it[0] $it[1]; $x+=300 }
for($i=0;$i -lt 4;$i++){ $x=340+($i*300); $g.DrawLine((New-Object Drawing.Pen($script:mint,5)),$x,375,$x+26,375); $g.FillPolygon((New-Object Drawing.SolidBrush($script:mint)),[Drawing.Point[]]@([Drawing.Point]::new($x+26,367),[Drawing.Point]::new($x+40,375),[Drawing.Point]::new($x+26,383))) }
Add-Card $g 70 540 700 190 'Core actions' 'Create | view | edit | delete`nSearch | filter | sort | paginate`nAdmin-only changes and deletion' $script:blue
Add-Card $g 820 540 710 190 'Record integrity' 'A farm references a farmer; planting references a farm and crop; harvest references a planting. Foreign keys protect these links.' $script:gold
Save-Page $p '03_workflow.png'

# ERD
$p=New-Canvas 'Relational data model' 'Main entities are separated from reusable fixed-value labels.' '03  /  ERD'
$g=$p[1]
Add-Node $g 65 245 330 175 'FARMER' 'farmer_id PK`nname | contact | address' $script:gold
Add-Node $g 470 245 330 175 'FARM' 'farm_id PK | farmer_id FK`narea | location | lookup IDs'
Add-Node $g 875 245 300 175 'PLANTING' 'planting_id PK`nfarm_id FK | crop_id FK'
Add-Node $g 1250 245 280 175 'HARVEST' 'harvest_id PK`nplanting_id FK | yield'
Add-Node $g 470 500 330 175 'CROP' 'crop_id PK`nname | variety | lookup IDs' $script:blue
foreach($pair in @(@(395,330,470,330),@(800,330,875,330),@(1175,330,1250,330),@(800,560,875,420))){ $g.DrawLine((New-Object Drawing.Pen($script:mint,4)),$pair[0],$pair[1],$pair[2],$pair[3]) }
Add-Card $g 875 485 655 210 '7 lookup tables' 'Soil type | irrigation type | crop category`nRecommended season | planting status`nHarvest quality | harvest remark' $script:gold
$g.DrawLine((New-Object Drawing.Pen($script:gold,3)) ,1175,420,1175,485)
Add-Card $g 65 710 735 100 'Authentication' 'USERS is separate from the five business entities.' $script:blue
Save-Page $p '04_erd.png'

# Normalization
$p=New-Canvas 'Normalization: UNF to 3NF' 'The progression starts with all five core modules in one record.' '04  /  DATABASE NORMALIZATION'
$g=$p[1]
Add-Card $g 65 230 350 405 'UNF' 'One wide record combines Farmer, Farm, Crop, Planting, and Harvest.`n`nNested planting/harvest groups repeat.' $script:red
Add-Card $g 455 230 350 405 '1NF' 'Flatten repeating groups into rows.`n`nEach cell holds one value; entity facts repeat across rows.' $script:gold
Add-Card $g 845 230 350 405 '2NF' 'Separate entity facts into relations with primary keys.`n`nRemove partial dependencies from composite keys.' $script:blue
Add-Card $g 1235 230 300 405 '3NF' 'Move reusable labels into lookup tables.`n`nKeep lookup IDs as foreign keys.' $script:mint
foreach($x in @(415,805,1195)){ $g.DrawLine((New-Object Drawing.Pen($script:mint,5)),$x,430,$x+30,430) }
$g.DrawString('Implemented target: 5 business tables + 7 lookup tables + USERS = 13 tables',$script:fontHead,(New-Object Drawing.SolidBrush($script:white)),95,700)
Save-Page $p '05_normalization.png'

# Requirements coverage
$p=New-Canvas 'Guideline coverage' 'Mapped to the original AP3 project guideline (ap3 project.docx).' '05  /  REQUIREMENTS'
$g=$p[1]
Add-Card $g 70 225 700 230 'Five required modules' 'Farmers | Farms | Crops`nPlantings | Harvests`nCRUD pages, validation, relationships, role rules' $script:mint
Add-Card $g 830 225 700 230 'Dashboard and six reports' 'Farmers and farms | Crops per farm`nPlantings by status | Harvest records`nTotal quantity | Estimated harvest value' $script:blue
Add-Card $g 70 500 700 230 'Navigation and usability' 'Authentication | Admin / Staff roles`nSearch | filters | sorting | pagination`nConfirmation for permanent deletion' $script:gold
Add-Card $g 830 500 700 230 'Database and devices' 'PostgreSQL | PK/FK | lookup tables`nResponsive layout and wide-table scrolling`nFinal populated-data and device evidence pending' $script:red
Save-Page $p '06_guideline_coverage.png'

# Roles and integrity
$p=New-Canvas 'Access and data integrity' 'Rules that protect records while keeping the interface understandable.' '06  /  SECURITY & QUALITY'
$g=$p[1]
Add-Card $g 70 235 450 225 'Administrator' 'View | create | edit | delete`nDeletion requires confirmation' $script:gold
Add-Card $g 575 235 450 225 'Staff' 'View records and reports`nNo create, edit, or delete actions' $script:blue
Add-Card $g 1080 235 450 225 'Application safeguards' 'Hashed passwords`nPrepared SQL statements`nEscaped output | validation' $script:mint
Add-Card $g 70 520 700 215 'Database safeguards' 'Primary keys and foreign keys`nRequired fields and range checks`nUnique constraints where defined' $script:mint
Add-Card $g 830 520 700 215 'Duplicate review is separate' '3NF removes dependency redundancy; it does not merge repeated people or farms. Candidate matches need identity review before cleanup.' $script:red
Save-Page $p '07_integrity.png'

# Readiness and screenshot spaces
$p=New-Canvas 'Final evidence checklist' 'This report does not claim unverified data or device results.' '07  /  COMPLETION STATUS'
$g=$p[1]
Add-Card $g 70 215 700 200 'Documented / implemented' 'Module structure | lookup-based schema`nRole rules | validation | reports`nNormalization explanation and ERD' $script:mint
Add-Card $g 830 215 700 200 'Still needs evidence' 'Final populated-data regression checks`nDuplicate identity review`nReplace synthetic records with verified real data' $script:red
Add-Card $g 70 465 700 265 'Student screenshot slot: phones' 'Insert actual iOS phone capture`n`nInsert actual Android phone capture`nShow date, page, and device/browser in caption' $script:blue
Add-Card $g 830 465 700 265 'Student screenshot slot: wider views' 'Insert desktop at 1440 CSS px`n`nInsert tablet at 768 CSS px (label as emulated)`nAdd test results and final demo notes' $script:gold
Save-Page $p '08_evidence.png'

# Core data dictionary
$p=New-Canvas 'Data dictionary | core tables' 'Key fields from the documented 3NF business schema.' '08  /  DATA DICTIONARY'
$g=$p[1]
Add-Card $g 70 220 700 180 'FARMER' 'farmer_id (PK) | first_name | last_name`ncontact_number | address | date_registered`ncreated_at | updated_at' $script:gold
Add-Card $g 830 220 700 180 'FARM' 'farm_id (PK) | farmer_id (FK) | farm_name`nlocation | area_hectares | soil_type_id (FK)`nirrigation_type_id (FK) | timestamps' $script:mint
Add-Card $g 70 425 700 180 'CROP' 'crop_id (PK) | crop_name | variety`ncrop_category_id (FK) | growing_period_days`nrecommended_season_id (FK) | timestamps' $script:blue
Add-Card $g 830 425 700 180 'PLANTING' 'planting_id (PK) | farm_id (FK) | crop_id (FK)`nplanting_date | area_planted | seed_quantity`nexpected_harvest_date | status_id (FK) | timestamps' $script:gold
Add-Card $g 70 630 1460 165 'HARVEST' 'harvest_id (PK) | planting_id (FK) | harvest_date | quantity_kg | selling_price_per_kg`nquality_grade_id (FK) | remark_id (FK) | created_at | updated_at' $script:mint
Save-Page $p '09_dictionary_core.png'

# Lookups and users dictionary
$p=New-Canvas 'Data dictionary | lookups and access' 'Fixed choices are stored once and selected by foreign key.' '09  /  DATA DICTIONARY'
$g=$p[1]
Add-Card $g 70 220 700 230 'Farm lookups' 'SOIL_TYPES: soil_type_id, soil_type_name`nIRRIGATION_TYPES: irrigation_type_id, irrigation_type_name' $script:gold
Add-Card $g 830 220 700 230 'Crop lookups' 'CROP_CATEGORIES: crop_category_id, crop_category_name`nRECOMMENDED_SEASONS: recommended_season_id, recommended_season_name' $script:blue
Add-Card $g 70 495 700 230 'Planting lookup' 'PLANTING_STATUSES: status_id, status_name`nUsed by PLANTING.status_id' $script:mint
Add-Card $g 830 495 700 230 'Harvest lookups and users' 'HARVEST_QUALITY_GRADES: quality_grade_id, quality_grade_name`nHARVEST_REMARKS: remark_id, remark_description`nUSERS: user_id, username (unique), password_hash, role, created_at' $script:gold
Save-Page $p '10_dictionary_lookups.png'

# User guide
$p=New-Canvas 'Quick user guide' 'Everyday route through the Agriculture Information System.' '10  /  USER GUIDE'
$g=$p[1]
Add-Card $g 70 225 450 220 '1. Sign in' 'Use an assigned account. Access is role-based.' $script:blue
Add-Card $g 575 225 450 220 '2. Choose a module' 'Open Farmers, Farms, Crops, Plantings, or Harvests from navigation.' $script:mint
Add-Card $g 1080 225 450 220 '3. Find records' 'Use search, filters, sorting, and page controls on list screens.' $script:gold
Add-Card $g 70 500 700 220 '4. Maintain records' 'Administrators can create and edit records. Check required fields and linked dropdown choices before saving.' $script:gold
Add-Card $g 830 500 700 220 '5. Review and sign out' 'Open details or reports to review linked information. Staff can view only. Sign out from the account menu.' $script:blue
Save-Page $p '11_user_guide.png'

# Testing status
$p=New-Canvas 'Test and submission status' 'Status is reported from the project tracker; open work is not marked as passed.' '11  /  VERIFICATION'
$g=$p[1]
Add-Card $g 70 225 700 230 'Completed checks on record' 'PHP syntax checks for changed files`nInput validation and integer-boundary checks`nDOCX package: 8-page report and 8 embedded visuals verified' $script:mint
Add-Card $g 830 225 700 230 'Still outstanding' 'Full populated Supabase CRUD/report retest`nReview possible duplicate identities before cleanup`nVerify real-record provenance and replace synthetic data' $script:red
Add-Card $g 70 500 700 230 'Device and screen evidence' 'Actual iOS and Android phone captures`nDesktop at 1440 CSS px`n768 CSS px tablet viewport, labeled simulated' $script:blue
Add-Card $g 830 500 700 230 'Finish before submission' 'Complete the test-results report`nRehearse the demonstration`nReplace the evidence placeholders with approved captures' $script:gold
Save-Page $p '12_test_status.png'

# Build a Word document containing the visual pages.
$work = Join-Path $PSScriptRoot ('_docx_build_' + [Guid]::NewGuid().ToString('N'))
$null = New-Item -ItemType Directory -Path $work
$media = Join-Path $work 'word/media'; $null = New-Item -ItemType Directory -Path $media -Force
$relsDir = Join-Path $work 'word/_rels'; $null = New-Item -ItemType Directory -Path $relsDir -Force
$pageFiles = Get-ChildItem -LiteralPath $pages -Filter '*.png' | Sort-Object Name
foreach($f in $pageFiles){ Copy-Item -LiteralPath $f.FullName -Destination (Join-Path $media $f.Name) }
$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/></Types>'
$rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>'
$style = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Segoe UI" w:hAnsi="Segoe UI"/><w:sz w:val="20"/></w:rPr></w:rPrDefault></w:docDefaults></w:styles>'
$docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
$body = ''
$index=0
foreach($f in $pageFiles){
  $index++; $rid='rId' + ($index+1); $docRels += '<Relationship Id="' + $rid + '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/' + $f.Name + '"/>'
  $cx=9144000; $cy=5143500
  $body += '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0"/></w:pPr><w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="' + $cx + '" cy="' + $cy + '"/><wp:docPr id="' + $index + '" name="' + $f.BaseName + '"/><wp:cNvGraphicFramePr><a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="1"/></wp:cNvGraphicFramePr><a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:nvPicPr><pic:cNvPr id="' + $index + '" name="' + $f.Name + '"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="' + $rid + '"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' + $cx + '" cy="' + $cy + '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>'
  if($index -lt $pageFiles.Count){ $body += '<w:p><w:r><w:br w:type="page"/></w:r></w:p>' }
}
$docRels += '</Relationships>'
$sect = '<w:sectPr><w:pgSz w:w="15840" w:h="12240" w:orient="landscape"/><w:pgMar w:top="360" w:right="500" w:bottom="360" w:left="500" w:header="180" w:footer="180" w:gutter="0"/></w:sectPr>'
$document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"><w:body>' + $body + $sect + '</w:body></w:document>'
Set-Content -LiteralPath (Join-Path $work '[Content_Types].xml') -Value $contentTypes -Encoding UTF8
$rootRelsDir=Join-Path $work '_rels'; $null=New-Item -ItemType Directory -Path $rootRelsDir -Force
Set-Content -LiteralPath (Join-Path $rootRelsDir '.rels') -Value $rels -Encoding UTF8
Set-Content -LiteralPath (Join-Path $work 'word/styles.xml') -Value $style -Encoding UTF8
Set-Content -LiteralPath (Join-Path $work 'word/document.xml') -Value $document -Encoding UTF8
Set-Content -LiteralPath (Join-Path $relsDir 'document.xml.rels') -Value $docRels -Encoding UTF8
$docx=Join-Path $PSScriptRoot 'AIS_Final_Project_Report_Repaired.docx'
if(Test-Path -LiteralPath $docx){ Remove-Item -LiteralPath $docx -Force }
$archive = [IO.Compression.ZipFile]::Open($docx,[IO.Compression.ZipArchiveMode]::Create)
foreach($file in Get-ChildItem -LiteralPath $work -Recurse -File){
    $entryName = $file.FullName.Substring($work.Length + 1).Replace('\','/')
    [IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive,$file.FullName,$entryName,[IO.Compression.CompressionLevel]::Optimal) | Out-Null
}
$archive.Dispose()
Remove-Item -LiteralPath $work -Recurse -Force
Get-Item -LiteralPath $docx | Select-Object FullName,Length
