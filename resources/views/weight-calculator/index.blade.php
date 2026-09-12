@extends('layouts.app')

@section('title', 'Weight Calculator')

@section('content')
<div class="breadcrumb-c"><a href="{{ route('dashboard') }}">Home</a><i class="bi bi-chevron-right"></i><span class="current">Weight Calculator</span></div>
<div class="page-header">
  <div>
    <div class="page-title">Weight Calculator</div>
    <div class="page-subtitle">Estimate metal weight and price by shape, material and dimensions — for quick quoting.</div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card-c">
      <div class="card-c-body">
        <div class="row g-3">
          <div class="col-md-6">
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
          <div class="col-md-6">
            <label class="form-label">Material</label>
            <select class="form-select" id="wcMaterial">
              @foreach ($densities as $key => $m)
                <option value="{{ $m['value'] }}">{{ $m['label'] }} ({{ $m['value'] }} g/cm³)</option>
              @endforeach
              <option value="custom">Custom density...</option>
            </select>
          </div>
          <div class="col-md-6" id="wcCustomDensityField" style="display:none;">
            <label class="form-label">Custom Density (g/cm³)</label>
            <input type="number" step="any" min="0" class="form-control" id="wcCustomDensity" placeholder="e.g. 7.85">
          </div>
        </div>

        <hr>

        <div class="row g-3" id="wcDims"></div>
        <div class="small text-muted-c mt-2">Each dimension has its own unit — pick mm, cm, m, inch, or foot separately for outer, inner/wall, and length.</div>

        <div class="row g-3 mt-1">
          <div class="col-md-6">
            <label class="form-label">Quantity (pieces)</label>
            <input type="number" min="1" step="1" class="form-control" id="wcQty" value="1">
          </div>
          <div class="col-md-6">
            <label class="form-label">Price per kg (₹)</label>
            <input type="number" step="any" min="0" class="form-control" id="wcPricePerKg" placeholder="e.g. 1000">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card-c h-100">
      <div class="card-c-body d-flex flex-column">
        <h5 class="mb-3"><i class="bi bi-calculator-fill me-1"></i>Result</h5>
        <div class="row g-3 text-center">
          <div class="col-6">
            <div class="small text-muted-c">Weight per piece</div>
            <div style="font-size:26px;font-weight:700;" id="wcPerPiece">0 kg</div>
          </div>
          <div class="col-6">
            <div class="small text-muted-c">Total weight (<span id="wcQtyLabel">1</span> pc)</div>
            <div style="font-size:26px;font-weight:700;color:var(--color-primary);" id="wcTotal">0 kg</div>
          </div>
          <div class="col-6">
            <div class="small text-muted-c">Price per piece</div>
            <div style="font-size:22px;font-weight:700;" id="wcPricePerPiece">₹0</div>
          </div>
          <div class="col-6">
            <div class="small text-muted-c">Total price</div>
            <div style="font-size:22px;font-weight:700;color:var(--color-success);" id="wcPriceTotal">₹0</div>
          </div>
        </div>
        <div class="small text-muted-c mt-4">Formulas: solid bars/pipes use cross-section area × length × density; sheet/plate uses length × width × thickness × density. Price = weight × price per kg. Figures are estimates — always confirm against your supplier's certified weight for final quotes.</div>
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
  var customField = document.getElementById('wcCustomDensityField');
  var customInput = document.getElementById('wcCustomDensity');
  var dimsWrap = document.getElementById('wcDims');
  var qtyInput = document.getElementById('wcQty');
  var priceInput = document.getElementById('wcPricePerKg');
  var perPieceEl = document.getElementById('wcPerPiece');
  var totalEl = document.getElementById('wcTotal');
  var qtyLabelEl = document.getElementById('wcQtyLabel');
  var pricePerPieceEl = document.getElementById('wcPricePerPiece');
  var priceTotalEl = document.getElementById('wcPriceTotal');

  // Conversion factor to millimetres -- all calculations happen in mm.
  var UNIT_TO_MM = { mm: 1, cm: 10, m: 1000, in: 25.4, ft: 304.8 };
  var UNIT_OPTIONS = ['mm', 'cm', 'm', 'in', 'ft'];

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
      var fieldUnits = {};
      dimsWrap.querySelectorAll('.wc-dim-unit').forEach(function (sel) {
        fieldUnits[sel.dataset.key] = sel.value;
      });
      var prefs = loadPrefs();
      prefs.shape = shapeSel.value;
      prefs.material = materialSel.value;
      prefs.fieldUnits = Object.assign({}, prefs.fieldUnits || {}, fieldUnits);
      localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
    } catch (e) { /* ignore (private browsing, storage disabled, etc.) */ }
  }

  function unitOptionsHtml(selected) {
    return UNIT_OPTIONS.map(function (u) {
      return '<option value="' + u + '"' + (u === selected ? ' selected' : '') + '>' + u + '</option>';
    }).join('');
  }

  function renderDimFields() {
    var fields = shapeFields[shapeSel.value] || [];
    var prefs = loadPrefs();
    var fieldUnits = prefs.fieldUnits || {};

    dimsWrap.innerHTML = fields.map(function (f) {
      var key = f[0], label = f[1];
      var unit = fieldUnits[key] || 'mm';
      return '<div class="col-md-6"><label class="form-label">' + label + '</label>' +
        '<div class="input-group input-group-sm">' +
        '<input type="number" step="any" min="0" class="form-control wc-dim" data-key="' + key + '">' +
        '<select class="form-select wc-dim-unit" data-key="' + key + '" style="max-width:78px;flex:0 0 auto;">' + unitOptionsHtml(unit) + '</select>' +
        '</div></div>';
    }).join('');

    dimsWrap.querySelectorAll('.wc-dim').forEach(function (input) {
      input.addEventListener('input', calculate);
    });
    dimsWrap.querySelectorAll('.wc-dim-unit').forEach(function (sel) {
      sel.addEventListener('change', function () { calculate(); savePrefs(); });
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
    var input = dimsWrap.querySelector('.wc-dim[data-key="' + key + '"]');
    var unitSel = dimsWrap.querySelector('.wc-dim-unit[data-key="' + key + '"]');
    if (!input) return 0;
    var raw = parseFloat(input.value) || 0;
    var factor = UNIT_TO_MM[unitSel ? unitSel.value : 'mm'] || 1;
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

  function formatRupees(n) {
    return '₹' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function calculate() {
    var shape = shapeSel.value;
    var density = currentDensity();
    var qty = Math.max(parseInt(qtyInput.value, 10) || 0, 0);
    var pricePerKg = parseFloat(priceInput.value) || 0;
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

    var totalWeightKg = weightKg * qty;

    perPieceEl.textContent = weightKg.toFixed(3) + ' kg';
    totalEl.textContent = totalWeightKg.toFixed(3) + ' kg';
    qtyLabelEl.textContent = qty;
    pricePerPieceEl.textContent = formatRupees(weightKg * pricePerKg);
    priceTotalEl.textContent = formatRupees(totalWeightKg * pricePerKg);
  }

  shapeSel.addEventListener('change', function () { renderDimFields(); savePrefs(); });
  materialSel.addEventListener('change', function () {
    customField.style.display = materialSel.value === 'custom' ? '' : 'none';
    calculate();
    savePrefs();
  });
  customInput.addEventListener('input', calculate);
  qtyInput.addEventListener('input', calculate);
  priceInput.addEventListener('input', calculate);

  // Restore last-used shape/material/per-field units for this browser, if any.
  var prefs = loadPrefs();
  if (prefs.shape && shapeFields[prefs.shape]) shapeSel.value = prefs.shape;
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
