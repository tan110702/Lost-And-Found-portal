const API = "backend/";
let allItems = [];
let selectedClaimItemId = null;

function getUser() {
  try {
    return JSON.parse(localStorage.getItem("user"));
  } catch {
    return null;
  }
}

function requireLogin() {
  if (!getUser()) {
    window.location.href = "login.html";
  }
}

function redirectIfLoggedIn() {
  if (getUser()) {
    window.location.href = "view.html";
  }
}

function showNotice(message, type = "success") {
  const notice = document.getElementById("notice");
  if (!notice) {
    alert(message);
    return;
  }

  notice.textContent = message;
  notice.className = `notice ${type}`;
}

function clearNotice() {
  const notice = document.getElementById("notice");
  if (notice) {
    notice.textContent = "";
    notice.className = "notice";
  }
}

function escapeHtml(value) {
  return String(value || "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function imagePath(fileName) {
  return fileName ? `uploads/${encodeURIComponent(fileName)}` : "";
}

function statusClass(status) {
  return status || "none";
}

async function postJson(url, payload) {
  const response = await fetch(API + url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(payload)
  });

  return response.json();
}

async function register() {
  clearNotice();
  const name = document.getElementById("name").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;

  if (!name || !email || !password) {
    showNotice("Please fill in all fields.", "error");
    return;
  }

  if (password.length < 6) {
    showNotice("Password must be at least 6 characters.", "error");
    return;
  }

  try {
    const data = await postJson("register.php", { name, email, password });
    if (data.message) {
      showNotice("Registered successfully. Redirecting to login...");
      setTimeout(() => window.location.href = "login.html", 700);
    } else {
      showNotice(data.error || "Registration failed.", "error");
    }
  } catch {
    showNotice("Could not reach the server.", "error");
  }
}

async function login() {
  clearNotice();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;

  if (!email || !password) {
    showNotice("Enter your email and password.", "error");
    return;
  }

  try {
    const data = await postJson("login.php", { email, password });
    if (data.user) {
      localStorage.setItem("user", JSON.stringify(data.user));
      window.location.href = "view.html";
    } else {
      showNotice(data.error || "Invalid credentials.", "error");
    }
  } catch {
    showNotice("Could not reach the server.", "error");
  }
}

function logout() {
  localStorage.removeItem("user");
  window.location.href = "login.html";
}

async function addItem() {
  clearNotice();
  const user = getUser();
  const title = document.getElementById("title").value.trim();
  const description = document.getElementById("desc").value.trim();
  const type = document.getElementById("type").value;
  const location = document.getElementById("location").value.trim();
  const image = document.getElementById("image").files[0];

  if (!user) {
    requireLogin();
    return;
  }

  if (!title || !description || !type || !location || !image) {
    showNotice("Please complete every field and choose an image.", "error");
    return;
  }

  const form = new FormData();
  form.append("title", title);
  form.append("description", description);
  form.append("type", type);
  form.append("location", location);
  form.append("user_id", user.id);
  form.append("image", image);

  try {
    const response = await fetch(API + "add_item.php", {
      method: "POST",
      body: form
    });
    const data = await response.json();

    if (data.message) {
      showNotice("Item posted successfully. Redirecting...");
      setTimeout(() => window.location.href = "view.html", 700);
    } else {
      showNotice(data.error || "Could not add item.", "error");
    }
  } catch {
    showNotice("Could not reach the server.", "error");
  }
}

function clearPostForm() {
  ["title", "desc", "location", "image"].forEach(id => {
    const field = document.getElementById(id);
    if (field) field.value = "";
  });
  const type = document.getElementById("type");
  if (type) type.value = "lost";
  clearNotice();
}

async function loadItems() {
  try {
    const response = await fetch(API + "get_items.php");
    const data = await response.json();
    allItems = Array.isArray(data) ? data : [];
    updateStats();
    applyItemFilters();
  } catch {
    showNotice("Could not load items.", "error");
  }
}

function updateStats() {
  const total = allItems.length;
  const lost = allItems.filter(item => item.type === "lost").length;
  const found = allItems.filter(item => item.type === "found").length;

  const totalCount = document.getElementById("totalCount");
  const lostCount = document.getElementById("lostCount");
  const foundCount = document.getElementById("foundCount");

  if (totalCount) totalCount.textContent = total;
  if (lostCount) lostCount.textContent = lost;
  if (foundCount) foundCount.textContent = found;
}

function applyItemFilters() {
  const keyword = (document.getElementById("search")?.value || "").toLowerCase();
  const type = document.getElementById("typeFilter")?.value || "all";
  const status = document.getElementById("statusFilter")?.value || "all";

  const filtered = allItems.filter(item => {
    const haystack = `${item.title} ${item.description} ${item.location}`.toLowerCase();
    const claimStatus = item.claim_status || "none";
    return haystack.includes(keyword)
      && (type === "all" || item.type === type)
      && (status === "all" || claimStatus === status);
  });

  displayItems(filtered);
}

function displayItems(items) {
  const container = document.getElementById("items");
  const user = getUser();

  if (!container) return;

  if (!items.length) {
    container.className = "";
    container.innerHTML = `<div class="empty">No items match your search yet.</div>`;
    return;
  }

  container.className = "grid";
  container.innerHTML = items.map(item => {
    const claimStatus = item.claim_status || "none";
    const ownItem = user && Number(user.id) === Number(item.user_id);
    const canClaim = !ownItem && claimStatus !== "approved";

    return `
      <article class="card">
        <img src="${imagePath(item.image)}" alt="${escapeHtml(item.title)}">
        <div class="card-body">
          <div>
            <span class="badge ${escapeHtml(item.type)}">${escapeHtml(item.type)}</span>
            <span class="badge ${statusClass(claimStatus)}">${claimStatus === "none" ? "No claims" : escapeHtml(claimStatus)}</span>
          </div>
          <h3>${escapeHtml(item.title)}</h3>
          <p>${escapeHtml(item.description)}</p>
          <p class="meta">${escapeHtml(item.location)}${item.created_at ? ` | ${escapeHtml(item.created_at)}` : ""}</p>
          <div class="card-actions">
            ${ownItem ? `<span class="muted">Posted by you</span>` : ""}
            ${canClaim ? `<button onclick="claimItem(${Number(item.id)})">Claim Item</button>` : ""}
          </div>
        </div>
      </article>
    `;
  }).join("");
}

function claimItem(itemId) {
  const user = getUser();
  if (!user) {
    requireLogin();
    return;
  }

  selectedClaimItemId = itemId;
  document.getElementById("claimPhone").value = "";
  document.getElementById("claimMessage").value = "";
  document.getElementById("claimModal").classList.add("open");
  document.getElementById("claimPhone").focus();
}

function closeClaimModal() {
  selectedClaimItemId = null;
  const modal = document.getElementById("claimModal");
  if (modal) modal.classList.remove("open");
}

async function submitClaim() {
  const user = getUser();
  const phone = document.getElementById("claimPhone").value.trim();
  const message = document.getElementById("claimMessage").value.trim() || "Phone verification submitted.";

  if (!user || !selectedClaimItemId) {
    closeClaimModal();
    requireLogin();
    return;
  }

  if (!/^[0-9+\-\s()]{7,20}$/.test(phone)) {
    showNotice("Enter a valid phone number for verification.", "error");
    return;
  }

  try {
    const data = await postJson("claim_item.php", {
      item_id: selectedClaimItemId,
      user_id: user.id,
      phone,
      message
    });

    if (data.message) {
      closeClaimModal();
      showNotice("Claim sent successfully.");
      loadItems();
    } else {
      showNotice(data.error || "Could not send claim.", "error");
    }
  } catch {
    showNotice("Could not reach the server.", "error");
  }
}

async function loadClaims() {
  const user = getUser();
  if (!user) {
    requireLogin();
    return;
  }

  try {
    const response = await fetch(`${API}get_claims.php?user_id=${encodeURIComponent(user.id)}&role=${encodeURIComponent(user.role || "user")}`);
    const claims = await response.json();
    displayClaims(Array.isArray(claims) ? claims : []);
  } catch {
    showNotice("Could not load claims.", "error");
  }
}

function displayClaims(claims) {
  const container = document.getElementById("claims");
  if (!container) return;

  if (!claims.length) {
    container.innerHTML = `<div class="empty">No claims have been submitted for your items yet.</div>`;
    return;
  }

  container.innerHTML = claims.map(claim => `
    <article class="card">
      <img src="${imagePath(claim.image)}" alt="${escapeHtml(claim.title)}">
      <div class="card-body">
        <span class="badge ${statusClass(claim.status)}">${escapeHtml(claim.status)}</span>
        <h3>${escapeHtml(claim.title)}</h3>
        <p><strong>Phone:</strong> ${escapeHtml(claim.phone)}</p>
        <p><strong>Verification note:</strong> ${escapeHtml(claim.message)}</p>
        <p class="meta">Claimed by ${escapeHtml(claim.claimant_name || "User")} (${escapeHtml(claim.claimant_email || "no email")})</p>
        <div class="card-actions">
          ${claim.status === "pending" ? `
            <button onclick="updateClaim(${Number(claim.id)}, 'approved')">Approve</button>
            <button class="danger" onclick="updateClaim(${Number(claim.id)}, 'rejected')">Reject</button>
          ` : `<span class="muted">Action completed</span>`}
        </div>
      </div>
    </article>
  `).join("");
}

async function updateClaim(id, status) {
  try {
    const data = await postJson("update_claim.php", { id, status });
    if (data.message) {
      showNotice("Claim updated.");
      loadClaims();
    } else {
      showNotice(data.error || "Could not update claim.", "error");
    }
  } catch {
    showNotice("Could not reach the server.", "error");
  }
}
