'use strict';

const CATEGORY_LABELS = { vegetables: 'Légumes', fruits: 'Fruits', herbs: 'Herbes aromatiques' };

let localImageData = '';
let products = [];

function byId(id) { return document.getElementById(id); }

function showMsg(message, type = 'error') {
  const box = byId('fd-form-message');
  if (!box) return;
  box.textContent = message;
  box.classList.remove('hidden', 'ok', 'error');
  box.classList.add(type);
}

function clearMsg() {
  const box = byId('fd-form-message');
  if (!box) return;
  box.classList.add('hidden');
  box.classList.remove('ok', 'error');
}

function updatePricingLabelField() {
  const type = byId('product-pricing-type')?.value;
  const wrap = byId('pricing-label-wrap');
  if (!wrap) return;
  wrap.classList.toggle('hidden', type !== 'custom');
}

function showImagePreview(src) {
  const wrap = byId('image-preview-wrap');
  const img  = byId('image-preview');
  if (!wrap || !img) return;
  if (src) {
    img.src = src;
    wrap.classList.remove('hidden');
  } else {
    wrap.classList.add('hidden');
    img.src = '';
  }
}

function resetForm() {
  byId('product-form')?.reset();
  byId('edit-id').value = '';
  byId('save-btn').innerHTML = '<i class="fas fa-floppy-disk"></i> Ajouter le produit';
  byId('cancel-edit-btn')?.classList.add('hidden');
  localImageData = '';
  showImagePreview('');
  const zoneText = byId('upload-zone-text');
  if (zoneText) zoneText.textContent = "Ou choisir depuis l'appareil";
  updatePricingLabelField();
  clearMsg();
}

function renderProducts() {
  const list = byId('farmer-product-list');
  if (!list) return;
  if (!products.length) {
    list.innerHTML = '<div class="fd-empty"><i class="fas fa-box-open"></i>Aucun produit ajouté pour le moment.</div>';
    return;
  }
  list.innerHTML = products.map(item => `
    <article class="fd-product-item">
      <img src="${item.image || 'https://placehold.co/200x200/e9f7ef/1e6b3c?text=+'}" alt="${item.name}">
      <div class="fd-product-body">
        <h3>${item.name}</h3>
        <p class="fd-meta">${item.category_name || CATEGORY_LABELS[item.category] || item.category}</p>
        <p class="fd-price">${item.price} DA <span style="font-weight:600;color:#8aab94;font-size:12px;">/ ${item.pricing_label}</span></p>
        <p class="fd-meta"><i class="fas fa-boxes-stacked" style="margin-right:4px;"></i>${item.available_qty} disponible${item.available_qty > 1 ? 's' : ''}</p>
        <div class="fd-item-actions">
          <button onclick="editProduct(${item.id})"><i class="fas fa-pen"></i> Modifier</button>
          <button onclick="deleteProduct(${item.id})"><i class="fas fa-trash"></i> Supprimer</button>
        </div>
      </div>
    </article>
  `).join('');
}

function editProduct(id) {
  const item = products.find(p => String(p.id) === String(id));
  if (!item) return;
  byId('edit-id').value = item.id;
  byId('product-category').value = item.category;
  byId('product-name').value = item.name;
  byId('product-price').value = item.price;
  const uiType = (item.pricing_type === 'custom' && item.pricing_label === 'bouquet') ? 'bouquet' : item.pricing_type;
  byId('product-pricing-type').value = uiType;
  byId('product-pricing-label').value = (item.pricing_type === 'custom' && item.pricing_label !== 'bouquet') ? item.pricing_label : '';
  byId('product-qty').value = item.available_qty;
  byId('product-description').value = item.description || '';
  const img = item.image || '';
  byId('product-image-url').value = img.startsWith('data:') ? '' : img;
  localImageData = img.startsWith('data:') ? img : '';
  showImagePreview(img);
  updatePricingLabelField();
  byId('save-btn').innerHTML = '<i class="fas fa-floppy-disk"></i> Enregistrer';
  byId('cancel-edit-btn')?.classList.remove('hidden');
  showMsg('Mode modification activé.', 'ok');
  byId('product-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function deleteProduct(id) {
  if (!confirm('Supprimer ce produit définitivement ?')) return;
  try {
    const res  = await fetch('../api/farmer/product-delete.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id }),
    });
    const json = await res.json();
    if (!json.success) { showMsg(json.message || 'Erreur lors de la suppression.'); return; }
    products = products.filter(p => String(p.id) !== String(id));
    renderProducts();
    showMsg('Produit supprimé.', 'ok');
  } catch {
    showMsg('Erreur réseau lors de la suppression.');
  }
}

function handleFileChange(event) {
  const file = event.target.files?.[0];
  if (!file) { localImageData = ''; showImagePreview(''); return; }
  const zoneText = byId('upload-zone-text');
  if (zoneText) zoneText.textContent = file.name;
  const reader = new FileReader();
  reader.onload = () => {
    localImageData = String(reader.result || '');
    showImagePreview(localImageData);
  };
  reader.readAsDataURL(file);
}

async function handleSubmit(event) {
  event.preventDefault();
  clearMsg();

  const editId      = byId('edit-id').value;
  const category    = byId('product-category').value;
  const name        = byId('product-name').value.trim();
  const price       = Number(byId('product-price').value);
  const pricingType = byId('product-pricing-type').value;
  const customLabel = byId('product-pricing-label').value.trim();
  const availableQty = Number(byId('product-qty').value);
  const imageUrl    = byId('product-image-url').value.trim();
  const description = byId('product-description').value.trim();
  const pricingLabel = pricingType === 'kg' ? 'kg' : pricingType === 'bouquet' ? 'bouquet' : customLabel;
  const image       = imageUrl || localImageData;

  if (!name || !price || !availableQty) { showMsg('Nom, prix et quantité sont obligatoires.'); return; }
  if (pricingType === 'custom' && !customLabel) { showMsg('Ajoutez le libellé de quantité.'); return; }
  if (pricingType === 'bouquet' && !pricingLabel) { showMsg('Libellé bouquet manquant.'); return; }

  const payload = { category, name, description, price, pricingType, pricingLabel, availableQty, image };
  if (editId) payload.id = Number(editId);

  const btn = byId('save-btn');
  btn.disabled = true;

  try {
    const res  = await fetch('../api/farmer/product-save.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const json = await res.json();
    if (!json.success) { showMsg(json.message || 'Erreur lors de la sauvegarde.'); return; }

    await loadProducts();
    resetForm();
    showMsg(editId ? 'Produit modifié avec succès.' : 'Produit ajouté avec succès.', 'ok');
  } catch {
    showMsg('Erreur réseau. Réessayez.');
  } finally {
    btn.disabled = false;
  }
}

async function loadProducts() {
  try {
    const res  = await fetch('../api/farmer/products.php');
    const json = await res.json();
    if (json.success) {
      products = json.products;
      renderProducts();
    }
  } catch {
    // keep existing state on network error
  }
}

async function logoutFarmer() {
  try { await fetch('../api/auth/logout.php', { method: 'POST' }); } catch {}
  window.location.href = '../index.php';
}

document.addEventListener('DOMContentLoaded', async () => {
  await loadProducts();
  updatePricingLabelField();

  byId('product-pricing-type')?.addEventListener('change', updatePricingLabelField);
  byId('product-image-file')?.addEventListener('change', handleFileChange);
  byId('product-image-url')?.addEventListener('input', e => {
    if (!localImageData) showImagePreview(e.target.value.trim());
  });
  byId('product-form')?.addEventListener('submit', handleSubmit);
  byId('cancel-edit-btn')?.addEventListener('click', resetForm);
  byId('logout-btn')?.addEventListener('click', logoutFarmer);
});

window.editProduct   = editProduct;
window.deleteProduct = deleteProduct;
