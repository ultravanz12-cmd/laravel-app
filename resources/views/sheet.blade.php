<!DOCTYPE html>
<html>
<head>
    <title>Spreadsheet Editor</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/plugins/css/pluginsCss.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/plugins/plugins.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/css/luckysheet.css">

    <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/plugins/js/plugin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/luckysheet.umd.js"></script>

    <style>
        body { margin:0; font-family: Arial; }
        #luckysheet { width:100%; height:95vh; }

        .topbar {
            padding:10px;
            display:flex;
            gap:10px;
            background:#fff;
            border-bottom:1px solid #ddd;
        }

        button {
            padding:6px 12px;
            cursor:pointer;
        }

        #loading {
            display:none;
            color:blue;
            font-weight:bold;
        }
    </style>
</head>
<body>

<div class="topbar">
    <a href="/"><button>⬅ Back</button></a>

    <button id="saveBtn" onclick="save()">💾 Save</button>

    <a href="{{ route('sheet.download', $sheet->id) }}">
        <button>📥 Download</button>
    </a>

    <span id="loading">Saving...</span>
</div>

<div id="luckysheet"></div>

<script>
let sheetData = @json($data ?? []);

$(function () {
    luckysheet.create({
        container: "luckysheet",
        data: sheetData.length ? sheetData : [{
            name: "Sheet1",
            celldata: [],
            row: 100,
            column: 26
        }],
        showinfobar: true,
        showtoolbar: true,
        allowEdit: true
    });
});

function save() {

    let btn = document.getElementById("saveBtn");
    btn.disabled = true;
    btn.innerText = "Saving...";

    let sheets = luckysheet.getAllSheets();

    let cleanSheets = sheets.map(s => ({
        name: s.name,
        row: s.row,
        column: s.column,

        // 🔥 ONLY CLEAN CELLS
        celldata: (s.celldata || []).map(c => ({
            r: c.r,
            c: c.c,
            v: (c.v && typeof c.v === "object") ? (c.v.v ?? "") : c.v
        }))
    }));

    fetch("/sheet/save/{{ $sheet->id }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ data: cleanSheets })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message || "Saved");
    })
    .catch(err => {
        console.error(err);
        alert("Save Failed");
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerText = "💾 Save";
    });
}
</script>

</body>
</html>