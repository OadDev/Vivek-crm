@extends('layouts.app')

@section('title', 'Weight Calculator')

@section('content')
<div class="breadcrumb-c"><a href="{{ route('dashboard') }}">Home</a><i class="bi bi-chevron-right"></i><span class="current">Weight Calculator</span></div>
<div class="page-header">
  <div>
    <div class="page-title">Weight Calculator</div>
    <div class="page-subtitle">Estimate metal weight by shape, material and dimensions — for quick quoting.</div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card-c">
      <div class="card-c-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Shape</label>
            <select class="form-select" id="wcShape">
              <option value="round_bar">Round Bar / Rod</option>
              <option value="square_bar">Square Bar</option>
              <option value="hex_bar">Hexagonal Bar</option>
              <option value="rect_bar">Rectangular Bar / Flat</option>
              <option value="round_pipe">Round Pipe / Tube</option>
              <option value="square_pipe">Square Pipe / Tube</option>
              <option value="sheet_plate">Sheet / Plate</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Material</label>
            <select class="form-select" id="wcMaterial">
              @foreach ($densities as $key => $m)
                <option value="{{ $m['value'] }}">{{ $m['label'] }} ({{ $m['value'] }} g/cm³)</option>
              @endforeach
              <option value="custom">Custom density...</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Unit</label>
            <select class="form-select" id="wcUnit">
              <option value="mm">Millimetre (mm)</option>
              <option value="cm">Centimetre (cm)</option>
              <option value="m">Metre (m)</option>
              <option value="in">Inch (in)</option>
              <option value="ft">Foot (ft)</option>
            </select>
          </div>
          <div class="col-md-4" id="wcCustomDensityField" style="display:none;">
            <label class="form-label">Custom Density (g/cm³)</label>
            <input type="number" step="any" min="0" class="form-control" id="wcCustomDensity" placeholder="e.g. 7.85">
          </div>
        </div>

        <hr>

        <div class="row g-3" id="wcDims"></div>

        <div class="row g-3 mt-1">
          <div class="col-md-6">
            <label class="form-label">Quantity (pieces)</label>
            <input type="number" min="1" step="1" class="form-control" id="wcQty" value="1">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card-c h-100">
      <div class="card-c-body d-flex flex-column">
        <h5 class="mb-3"><i class="bi bi-calculator-fill me-1"></i>Result</h5>
        <div class="flex-fill d-flex flex-column justify-content-center align-items-center text-center" style="gap:6px;">
          <div class="small text-muted-c">Weight per piece</div>
          <div style="font-size:32px;font-weight:700;" id="wcPerPiece">0 kg</div>
          <div class="small text-muted-c mt-3">Total weight (<span id="wcQtyLabel">1</span> piece(s))</div>
          <div style="font-size:24px;font-weight:700;color:var(--color-primary);" id="wcTotal">0 kg</div>
        </div>
        <div class="small text-muted-c mt-4">Enter dimensions in whichever unit you pick above — everything converts automatically. Formulas: solid bars/pipes use cross-section area × length × density; sheet/plate uses length × width × thickness × density. Figures are estimates — always confirm against your supplier's certified weight for final quotes.</div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var STORAGE_KEY = 'weight_calc_prefs';

  var shapeSel = document.getElementById('wcShape');
  var materialSel = document.getElementById('wcMaterial');
  var unitSel = document.getElementById('wcUnit');
  var customField = document.getElementById('wcCustomDensityField');
  var customInput = document.getElementById('wcCustomDensity');
  var dimsWrap = document.getElementById('wcDims');
  var qtyInput = document.getElementById('wcQty');
  var perPieceEl = document.getElementById('wcPerPiece');
  var totalEl = document.getElementById('wcTotal');
  var qtyLabelEl = document.getElementById('wcQtyLabel');

  // Conversion factor to millimetres -- all calculations happen in mm.
  var UNIT_TO_MM = { mm: 1, cm: 10, m: 1000, in: 25.4, ft: 304.8 };
  var UNIT_LABEL = { mm: 'mm', cm: 'cm', m: 'm', in: 'in', ft: 'ft' };

  var shapeFields = {
    round_bar: [['d', 'Diameter']],
    square_bar: [['side', 'Side']],
    hex_bar: [['af', 'Across Flats']],
    rect_bar: [['width', 'Width'], ['thickness', 'Thickness']],
    round_pipe: [['od', 'Outer Diameter'], ['wt', 'Wall Thickness']],
    square_pipe: [['side', 'Outer Side'], ['wt', 'Wall Thickness']],
    sheet_plate: [['length', 'Length'], ['width', 'Width'], ['thickness', 'Thickness']],
  };
  // Every shape except sheet/plate also needs a Length field, added last.
  Object.keys(shapeFields).forEach(function (shape) {
    if (shape !== 'sheet_plate') shapeFields[shape].push(['length', 'Length']);
  });

  function loadPrefs() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
    } catch (e) {
      return {};
    }
  }

  function savePrefs() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({
        shape: shapeSel.value,
        material: materialSel.value,
        unit: unitSel.value,
      }));
    } catch (e) { /* ignore (private browsing, storage disabled, etc.) */ }
  }

  function unitLabel() {
    return UNIT_LABEL[unitSel.value] || 'mm';
  }

  function renderDimFields() {
    var fields = shapeFields[shapeSel.value] || [];
    var unit = unitLabel();
    dimsWrap.innerHTML = fields.map(function (f) {
      return '<div class="col-md-6"><label class="form-label">' + f[1] + ' (' + unit + ')</label>' +
        '<input type="number" step="any" min="0" class="form-control wc-dim" data-key="' + f[0] + '"></div>';
    }).join('');
    dimsWrap.querySelectorAll('.wc-dim').forEach(function (input) {
      input.addEventListener('input', calculate);
    });
    calculate();
  }

  function relabelDimFields() {
    var unit = unitLabel();
    dimsWrap.querySelectorAll('.wc-dim').forEach(function (input) {
      var label = input.closest('.col-md-6').querySelector('.form-label');
      var base = label.textContent.replace(/\s*\([^)]*\)\s*$/, '');
      label.textContent = base + ' (' + unit + ')';
    });
    calculate();
  }

  function currentDensity() {
    if (materialSel.value === 'custom') {
      return parseFloat(customInput.value) || 0;
    }
    return parseFloat(materialSel.value) || 0;
  }

  function dimValueMm(key) {
    var el = dimsWrap.querySelector('.wc-dim[data-key="' + key + '"]');
    if (!el) return 0;
    var raw = parseFloat(el.value) || 0;
    var factor = UNIT_TO_MM[unitSel.value] || 1;
    return raw * factor;
  }

  function crossSectionArea(shape) {
    switch (shape) {
      case 'round_bar':
        var d = dimValueMm('d');
        return Math.PI / 4 * d * d;
      case 'square_bar':
        var side = dimValueMm('side');
        return side * side;
      case 'hex_bar':
        var af = dimValueMm('af');
        return 0.8660254 * af * af;
      case 'rect_bar':
        return dimValueMm('width') * dimValueMm('thickness');
      case 'round_pipe':
        var od = dimValueMm('od'), wt = dimValueMm('wt');
        var id = Math.max(od - 2 * wt, 0);
        return Math.PI / 4 * (od * od - id * id);
      case 'square_pipe':
        var outerSide = dimValueMm('side'), wallT = dimValueMm('wt');
        var innerSide = Math.max(outerSide - 2 * wallT, 0);
        return outerSide * outerSide - innerSide * innerSide;
      default:
        return 0;
    }
  }

  function calculate() {
    var shape = shapeSel.value;
    var density = currentDensity();
    var qty = Math.max(parseInt(qtyInput.value, 10) || 0, 0);
    var weightKg = 0;

    if (shape === 'sheet_plate') {
      var volumeMm3 = dimValueMm('length') * dimValueMm('width') * dimValueMm('thickness');
      weightKg = volumeMm3 * density / 1e6;
    } else {
      var area = crossSectionArea(shape);
      var length = dimValueMm('length');
      weightKg = area * length * density / 1e6;
    }

    if (!isFinite(weightKg) || weightKg < 0) weightKg = 0;

    perPieceEl.textContent = weightKg.toFixed(3) + ' kg';
    totalEl.textContent = (weightKg * qty).toFixed(3) + ' kg';
    qtyLabelEl.textContent = qty;
  }

  shapeSel.addEventListener('change', function () { renderDimFields(); savePrefs(); });
  materialSel.addEventListener('change', function () {
    customField.style.display = materialSel.value === 'custom' ? '' : 'none';
    calculate();
    savePrefs();
  });
  unitSel.addEventListener('change', function () { relabelDimFields(); savePrefs(); });
  customInput.addEventListener('input', calculate);
  qtyInput.addEventListener('input', calculate);

  // Restore last-used shape/material/unit for this browser, if any.
  var prefs = loadPrefs();
  if (prefs.shape && shapeFields[prefs.shape]) shapeSel.value = prefs.shape;
  if (prefs.unit && UNIT_TO_MM[prefs.unit]) unitSel.value = prefs.unit;
  if (prefs.material) {
    var hasOption = Array.prototype.some.call(materialSel.options, function (o) { return o.value === prefs.material; });
    materialSel.value = hasOption ? prefs.material : 'custom';
    if (materialSel.value === 'custom') {
      customField.style.display = '';
      if (!hasOption) customInput.value = prefs.material;
    }
  }

  renderDimFields();
});
</script>
@endpush
