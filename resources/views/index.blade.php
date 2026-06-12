<!DOCTYPE html>
<html>
<head>
    <title>Spreadsheet Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; font-family: Arial, sans-serif; background: #f9f9f9; }
        .container { max-width: 900px; margin: auto; }
        h1 { margin-bottom: 20px; }
        .sheet-list { margin-top: 30px; }
        .sheet-item { padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .sheet-item a { text-decoration: none; }
    </style>
</head>
<body>

<div class="container">
    <h1>Spreadsheet Manager</h1>

    <!-- Upload Form -->
    <div class="card p-4 mb-4">
        <form action="{{ route('sheet.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="file" class="form-label">Upload Excel/CSV File</label>
                <input type="file" name="file" id="file" class="form-control" required>
            </div>
            <button class="btn btn-primary">Upload & Edit</button>
        </form>
    </div>

    <!-- Uploaded Sheets List -->
    <div class="sheet-list">
        <h3>Uploaded Sheets</h3>
        @if($sheets->count() > 0)
            @foreach($sheets as $sheet)
                <div class="sheet-item">
                    <span>{{ $sheet->name }}</span>
                    <a href="{{ route('sheet.edit', $sheet->id) }}" class="btn btn-sm btn-success">Open/Edit</a>
                </div>
            @endforeach
        @else
            <p>No sheets uploaded yet.</p>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>