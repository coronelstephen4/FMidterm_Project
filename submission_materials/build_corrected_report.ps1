Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$ErrorActionPreference = 'Stop'

$outPath = Join-Path $PSScriptRoot 'AIS_Final_Project_Report_Corrected.docx'
$temp = Join-Path $PSScriptRoot ('_corrected_docx_' + [Guid]::NewGuid().ToString('N'))
$null = New-Item -ItemType Directory -Path (Join-Path $temp 'word/_rels') -Force
$null = New-Item -ItemType Directory -Path (Join-Path $temp '_rels') -Force

function X([string]$s) { [System.Security.SecurityElement]::Escape($s) }
function New-P([string]$s, [string]$style='') {
    $pr = if ($style) { '<w:pPr><w:pStyle w:val="' + $style + '"/></w:pPr>' } else { '' }
    return '<w:p>' + $pr + '<w:r><w:t xml:space="preserve">' + (X $s) + '</w:t></w:r></w:p>'
}
function New-H([string]$s, [int]$level=1) { New-P $s ('Heading' + $level) }
function T([string[]]$headers, [object[]]$rows) {
    $xml = '<w:tbl><w:tblPr><w:tblStyle w:val="TableGrid"/><w:tblW w:w="0" w:type="auto"/><w:tblLook w:firstRow="1" w:lastRow="0" w:firstColumn="0" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/></w:tblPr><w:tblGrid>'
    for ($i=0; $i -lt $headers.Count; $i++) { $xml += '<w:gridCol w:w="2200"/>' }
    $xml += '</w:tblGrid><w:tr>'
    foreach ($head in $headers) { $xml += '<w:tc><w:tcPr><w:shd w:fill="DDEFE9"/></w:tcPr>' + (New-P $head) + '</w:tc>' }
    $xml += '</w:tr>'
    foreach ($row in $rows) {
        $xml += '<w:tr>'
        foreach ($cell in $row) {
            $xml += '<w:tc><w:tcPr><w:vAlign w:val="top"/></w:tcPr>'
            foreach ($line in ([string]$cell -split "`n")) { $xml += New-P $line }
            $xml += '</w:tc>'
        }
        $xml += '</w:tr>'
    }
    return $xml + '</w:tbl>'
}

$body = ''
$body += New-P 'AGRICULTURE INFORMATION SYSTEM' 'Title'
$body += New-P 'AP3 Midterm Project | Final Project Documentation'
$body += New-P 'Cebu Technological University'
$body += New-P 'Native PHP + PDO | Supabase PostgreSQL | Tailwind CSS'
$body += New-P 'Revised 12 November 2026. Prepared against the original ap3 project.docx and the current project documentation.'
$body += New-H '1. Project description and objectives'
$body += New-P 'The Agriculture Information System records farmers, farms, crop varieties, planting operations, and harvest yields. It provides role-based access, validated record management, a dashboard, and reports.'
$body += New-P 'Objectives: organize records in a relational database; demonstrate normalization through 3NF, keys, relationships, and constraints; protect access with hashed passwords and roles; validate input; and summarize planting and harvest activity.'
$body += New-H '2. Functional requirements and implementation'
$fr = @(
    ,@('Requirement','System coverage / evidence status'),
    ,@('Core modules','Farmers, Farms, Crops, Plantings, and Harvests have list and record-maintenance pages. Final populated-data regression checks remain open.'),
    ,@('Controlled choices','Soil, irrigation, crop category, recommended season, planting status, harvest quality, and harvest remarks use lookup-backed choices.'),
    ,@('Relationships and reports','Related values are joined for display. Dashboard and six report topics cover farmers/farms, crops per farm, planting status, harvests, quantity, and estimated value.'),
    ,@('Access and integrity','Administrator can maintain records; Staff is view-only. Password hashes, validation, keys, foreign keys, unique/range rules, and delete safeguards are used.'),
    ,@('Responsive evidence','Responsive styling is present. Student evidence is still required from physical iOS and Android devices, desktop at 1440 CSS px, and simulated tablet at 768 CSS px.')
)
$body += T $fr[0] ($fr | Select-Object -Skip 1)
$body += New-H '3. Entity relationships'
$body += New-P 'FARMER 1:M FARM; FARM 1:M PLANTING; CROP 1:M PLANTING; PLANTING 1:M HARVEST. Each lookup relation has a one-to-many relationship with the core relation that stores its foreign key.'
$rel = @(
    ,@('Parent','Cardinality','Child','Meaning'),
    ,@('FARMER','1:M','FARM','A farmer may own multiple farms; each farm references one farmer.'),
    ,@('FARM','1:M','PLANTING','A farm may have multiple planting records.'),
    ,@('CROP','1:M','PLANTING','A crop variety may be selected by multiple planting records.'),
    ,@('PLANTING','1:M','HARVEST','A planting may have multiple harvest records.'),
    ,@('Lookup relation','1:M','Core relation','The core row stores the lookup ID; the label is stored once in the lookup table.')
)
$body += T $rel[0] ($rel | Select-Object -Skip 1)
$body += New-H '4. Normalization: assignment source through implemented 3NF'
$body += New-P 'Important source interpretation: ap3 project.docx defines five separate source tables. It does not provide one combined, wide UNF relation or nested repeating groups. The progression below therefore starts from those five tables and does not invent a wide record. The listed attributes are atomic. Each source relation has a single-column ID primary key, so there is no partial dependency on a composite key.'
$norm = @(
    ,@('Source relation','UNF / assignment starting table','1NF','2NF','3NF / implemented relation'),
    ,@('FARMER','farmer_id PK; first_name; last_name; contact_number; address; date_registered','Same six fields; one scalar value per field.','Same fields; farmer_id is a single-column PK.','farmer_id PK; first_name; last_name; contact_number; address; date_registered; created_at; updated_at.'),
    ,@('FARM','farm_id PK; farmer_id FK; farm_name; location; area_hectares; soil_type; irrigation_type','Same fields; each field contains one value.','Same entity; farm_id is a single-column PK.','farm_id PK; farmer_id FK; farm_name; location; area_hectares; soil_type_id FK; irrigation_type_id FK; timestamps.'),
    ,@('CROP','crop_id PK; crop_name; variety; crop_category; growing_period_days; recommended_season','Same fields; each field contains one value.','Same entity; crop_id is a single-column PK.','crop_id PK; crop_name; variety; crop_category_id FK; growing_period_days; recommended_season_id FK; timestamps.'),
    ,@('PLANTING','planting_id PK; farm_id FK; crop_id FK; planting_date; area_planted; seed_quantity; expected_harvest_date; status','Same fields; one planting per record.','Same entity; planting_id is a single-column PK.','planting_id PK; farm_id FK; crop_id FK; planting_date; area_planted; seed_quantity; expected_harvest_date; status_id FK; timestamps.'),
    ,@('HARVEST','harvest_id PK; planting_id FK; harvest_date; quantity_kg; selling_price_per_kg; quality_grade; remarks','Same fields; one harvest per record.','Same entity; harvest_id is a single-column PK.','harvest_id PK; planting_id FK; harvest_date; quantity_kg; selling_price_per_kg; quality_grade_id FK; remark_id FK; timestamps.')
)
$body += T $norm[0] ($norm | Select-Object -Skip 1)
$body += New-P '1NF outcome: the assignment lists atomic scalar attributes and no repeating groups, so no flattening step is specified. 2NF outcome: all five primary keys are single-column IDs, so partial-key dependencies cannot occur. In the implemented 3NF design, fixed-choice labels are moved into lookup relations as required by the assignment; the core rows hold foreign keys. This controlled-value design avoids storing the same label text in every business row.'
$body += New-H '5. Complete implemented relation inventory (13 relations)'
$body += New-P 'Count: 12 operational relations (5 core + 7 lookups) and 1 separate authentication-support relation (users). The users relation is included in the total application database count but is not one of the agriculture relations in the normalization exercise.'
$inventory = @(
    ,@('No.','Relation','Columns with types and key marks (PK / FK / UNIQUE)'),
    ,@('1','farmer','farmer_id integer (PK); first_name varchar(50); last_name varchar(50); contact_number varchar(20); address varchar(150); date_registered date; created_at timestamptz; updated_at timestamptz'),
    ,@('2','farm','farm_id integer (PK); farmer_id integer (FK); farm_name varchar(100); location varchar(150); area_hectares numeric(8,2); soil_type_id smallint (FK); irrigation_type_id smallint (FK); created_at timestamptz; updated_at timestamptz'),
    ,@('3','crop','crop_id integer (PK); crop_name varchar(80); variety varchar(80); crop_category_id smallint (FK); growing_period_days integer; recommended_season_id smallint (FK); created_at timestamptz; updated_at timestamptz'),
    ,@('4','planting','planting_id integer (PK); farm_id integer (FK); crop_id integer (FK); planting_date date; area_planted numeric(8,2); seed_quantity numeric(8,2); expected_harvest_date date; status_id smallint (FK); created_at timestamptz; updated_at timestamptz'),
    ,@('5','harvest','harvest_id integer (PK); planting_id integer (FK); harvest_date date; quantity_kg numeric(10,2); selling_price_per_kg numeric(10,2); quality_grade_id smallint (FK); remark_id integer (FK); created_at timestamptz; updated_at timestamptz'),
    ,@('6','soil_types','soil_type_id smallint (PK); soil_type_name varchar(50) (UNIQUE)'),
    ,@('7','irrigation_types','irrigation_type_id smallint (PK); irrigation_type_name varchar(50) (UNIQUE)'),
    ,@('8','crop_categories','crop_category_id smallint (PK); crop_category_name varchar(50) (UNIQUE)'),
    ,@('9','recommended_seasons','recommended_season_id smallint (PK); recommended_season_name varchar(30) (UNIQUE)'),
    ,@('10','planting_statuses','status_id smallint (PK); status_name varchar(30) (UNIQUE)'),
    ,@('11','harvest_quality_grades','quality_grade_id smallint (PK); quality_grade_name varchar(30) (UNIQUE)'),
    ,@('12','harvest_remarks','remark_id integer (PK); remark_description varchar(50) (UNIQUE)'),
    ,@('13','users (auth support)','user_id integer (PK); username varchar(50) (UNIQUE); password_hash varchar(255); role varchar(20); created_at timestamptz')
)
$body += T $inventory[0] ($inventory | Select-Object -Skip 1)
$body += New-H '6. Key constraints and controlled values'
$body += New-P 'Primary and foreign keys preserve entity links. Lookup labels are unique. Application and database rules cover required values, positive areas and quantities, nonnegative selling price, valid role values, date order, and uniqueness rules such as farm name per farmer and crop name plus variety. Passwords are stored as hashes.'
$body += New-P 'Lookup values include: soil (Loam, Clay Loam, Sandy Loam, Clay); irrigation (Rainfed, Irrigated, Drip Irrigation); category (Cereal, Vegetable, Fruit, Legume, Root Crop); season (Wet Season, Dry Season, Year Round); status (Harvested, Growing); quality (Grade A, Grade B, Grade C); remarks (Good Harvest, Acceptable Quality, Low Yield, Poor Quality, Damaged Harvest).'
$body += New-H '7. User guide'
$guide = @(
    ,@('Step','Action'),
    ,@('1. Sign in','Use an assigned account. The system applies the account role.'),
    ,@('2. Open a module','Use Farmers, Farms, Crops, Plantings, or Harvests. Use search, filters, sorting, and pagination to find rows.'),
    ,@('3. Review linked data','Open a record or report to view related farmer, farm, crop, and lookup labels.'),
    ,@('4. Maintain records','Administrators create and edit records. Choose controlled values from dropdowns and correct validation messages before saving.'),
    ,@('5. Delete / sign out','Only administrators can delete, with confirmation. Sign out from the Settings menu.')
)
$body += T $guide[0] ($guide | Select-Object -Skip 1)
$body += New-H '8. Verification and remaining evidence'
$body += New-P 'The project tracker records successful PHP syntax and input-validation checks for selected changes. Full populated-data regression across CRUD, search, filters, sorting, dropdowns, joins, authentication, dashboard, and reports remains pending. Candidate duplicate farmer identities require review; normalization does not automatically merge people. Data provenance and final real-record review remain open.'
$ev = @(
    ,@('Evidence item','Status / action'),
    ,@('Working-system screenshots','Student to insert captures of all required modules.'),
    ,@('Phone views','Student to capture physical iOS and Android views.'),
    ,@('Tablet and desktop','Student to capture 768 CSS-pixel simulated tablet and 1440 CSS-pixel desktop views; label tablet as simulated.'),
    ,@('Final test results','Complete the test-results worksheet after running final workflows against approved data.'),
    ,@('Database migration','Migration 08 to lookup IDs was confirmed applied by the user. This documentation revision made no Supabase schema or data changes.')
)
$body += T $ev[0] ($ev | Select-Object -Skip 1)
$body += New-P 'Source: ap3 project.docx (assignment guideline), PROJECT_REQUIREMENTS.md, PROJECT_DOCUMENTATION.md, and PROJECT_TRACKER.md.'

$ns = 'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
$doc = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document ' + $ns + '><w:body>' + $body + '<w:sectPr><w:pgSz w:w="15840" w:h="12240" w:orient="landscape"/><w:pgMar w:top="720" w:right="720" w:bottom="720" w:left="720" w:header="360" w:footer="360" w:gutter="0"/></w:sectPr></w:body></w:document>'
$types = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/></Types>'
$rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>'
$docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>'
$styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:rPr><w:rFonts w:ascii="Aptos" w:hAnsi="Aptos"/><w:sz w:val="18"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:pPr><w:jc w:val="center"/><w:spacing w:after="260"/></w:pPr><w:rPr><w:b/><w:color w:val="05694E"/><w:sz w:val="36"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:pPr><w:keepNext/><w:spacing w:before="260" w:after="120"/></w:pPr><w:rPr><w:b/><w:color w:val="05694E"/><w:sz w:val="26"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:pPr><w:keepNext/><w:spacing w:before="200" w:after="100"/></w:pPr><w:rPr><w:b/><w:color w:val="05694E"/><w:sz w:val="22"/></w:rPr></w:style><w:style w:type="table" w:default="1" w:styleId="TableGrid"><w:name w:val="Table Grid"/><w:tblPr><w:tblBorders><w:top w:val="single" w:sz="4" w:color="B7C7D4"/><w:left w:val="single" w:sz="4" w:color="B7C7D4"/><w:bottom w:val="single" w:sz="4" w:color="B7C7D4"/><w:right w:val="single" w:sz="4" w:color="B7C7D4"/><w:insideH w:val="single" w:sz="4" w:color="B7C7D4"/><w:insideV w:val="single" w:sz="4" w:color="B7C7D4"/></w:tblBorders></w:tblPr></w:style></w:styles>'
[System.IO.File]::WriteAllText((Join-Path $temp '[Content_Types].xml'),$types,[System.Text.UTF8Encoding]::new($false))
[System.IO.File]::WriteAllText((Join-Path $temp '_rels/.rels'),$rootRels,[System.Text.UTF8Encoding]::new($false))
[System.IO.File]::WriteAllText((Join-Path $temp 'word/document.xml'),$doc,[System.Text.UTF8Encoding]::new($false))
[System.IO.File]::WriteAllText((Join-Path $temp 'word/styles.xml'),$styles,[System.Text.UTF8Encoding]::new($false))
[System.IO.File]::WriteAllText((Join-Path $temp 'word/_rels/document.xml.rels'),$docRels,[System.Text.UTF8Encoding]::new($false))
if (Test-Path -LiteralPath $outPath) { Remove-Item -LiteralPath $outPath -Force }
$zip = [IO.Compression.ZipFile]::Open($outPath,[IO.Compression.ZipArchiveMode]::Create)
foreach ($file in Get-ChildItem -LiteralPath $temp -Recurse -File) {
    $name = $file.FullName.Substring($temp.Length + 1).Replace('\','/')
    [IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip,$file.FullName,$name,[IO.Compression.CompressionLevel]::Optimal) | Out-Null
}
$zip.Dispose()
Remove-Item -LiteralPath $temp -Recurse -Force
Get-Item -LiteralPath $outPath | Select-Object FullName,Length

