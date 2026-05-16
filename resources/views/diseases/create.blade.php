@extends('layouts.app')
@section('title', 'Upload Disease Image')
@section('breadcrumb', 'Disease Detection / Upload')

@section('content')
<style>
    .upload-container { max-width: 640px; margin: 0 auto; }
    .upload-back { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; }
    .upload-back a { color: var(--text-muted); text-decoration: none; transition: color 0.2s; }
    .upload-back a:hover { color: var(--primary); }
    .upload-card { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; }
    .upload-label { display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; }
    .upload-select { width: 100%; padding: 0.65rem 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem; color: var(--text-main); background: var(--bg-body); outline: none; }
    .upload-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(33,150,83,0.1); }
    .upload-zone { border: 2px dashed var(--border-color); border-radius: 12px; padding: 3rem; text-align: center; cursor: pointer; transition: all 0.3s; background: var(--bg-body); }
    .upload-zone:hover { border-color: var(--primary); background: rgba(33,150,83,0.02); }
    .upload-icon { width: 60px; height: 60px; margin: 0 auto 1rem; border-radius: 16px; background: rgba(33,150,83,0.1); display: flex; align-items: center; justify-content: center; }
    .upload-icon i { font-size: 1.5rem; color: var(--primary); }
    .upload-btn { width: 100%; padding: 0.85rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: all 0.2s; }
    .upload-btn:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(33,150,83,0.2); }
    .how-step { display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 1rem; }
    .how-num { width: 28px; height: 28px; border-radius: 8px; background: rgba(33,150,83,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.8rem; font-weight: 700; color: var(--primary); }
    .how-text { font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; margin-top: 0.15rem; }
</style>

<div class="upload-container">
    <div class="upload-back">
        <a href="{{ route('diseases.index') }}"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0;">Upload Crop Image</h1>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">Upload a leaf or crop image for AI disease analysis</p>
        </div>
    </div>

    <div class="upload-card">
        <form method="POST" action="{{ route('diseases.store') }}" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label class="upload-label">Select Farm</label>
                <select name="farm_id" required class="upload-select">
                    @foreach($farms as $farm)
                    <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="upload-label">Crop Image</label>
                <div class="upload-zone" onclick="document.getElementById('image-input').click()">
                    <div id="image-preview" style="display: none; margin-bottom: 1rem;">
                        <img id="preview-img" src="" style="max-height: 200px; margin: 0 auto; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    </div>
                    <div id="upload-placeholder">
                        <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <p style="font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">Click to upload or drag and drop</p>
                        <p style="font-size: 0.8rem; color: var(--text-muted); font-family: monospace;">PNG, JPG, JPEG up to 5MB</p>
                    </div>
                    <input type="file" name="image" id="image-input" accept="image/*" required style="display: none;" onchange="previewImage(this)">
                </div>
                @error('image')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.5rem;">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="upload-btn"><i class="fas fa-brain"></i> Analyze with AI</button>
        </form>
    </div>

    <div class="upload-card">
        <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem;">How it works</h4>
        <div class="how-step"><div class="how-num">1</div><p class="how-text">Upload a clear photo of the affected crop or leaf directly from your device.</p></div>
        <div class="how-step"><div class="how-num">2</div><p class="how-text">Our AI model analyzes the image for known disease patterns, lesions, and discoloration.</p></div>
        <div class="how-step"><div class="how-num">3</div><p class="how-text">Receive an instant diagnosis along with detailed treatment and prevention recommendations.</p></div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
            document.getElementById('upload-placeholder').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
