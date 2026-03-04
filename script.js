// @ts-nocheck
document.addEventListener('DOMContentLoaded', () => {
    loadUserData();  // this loads user data.
});
//right sidebar toggle
const toggleBtn = document.querySelector('.togglebtn');
const sidebar = document.getElementById('sidebar1');

if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        if (sidebar) sidebar.classList.toggle('hidden');
    });
}

// Data model
let docSpaceData = {
    categories: []
};

let currentDocId = null;
window.currentCategoryId = null;

// Render categories and documents
function renderCategories() {
    const container = document.getElementById('category-container');
    if (!container) return;
    container.innerHTML = '';

    docSpaceData.categories.forEach(cat => {
        const categoryDiv = document.createElement('div');
        categoryDiv.className = 'category';
        categoryDiv.dataset.id = cat.id;
        categoryDiv.innerHTML = `
            <div class="category-header">
                <span class="category-name" title="Double-click to rename">${escapeHtml(cat.name)}</span>
                <div class="category-actions">
                    <div class="import-menu">
                        <button class="control-btn import-btn" title="Import">Import</button>
                        <div class="import-options" data-cat-id="${cat.id}">
                            <button class="import-google">From Google</button><br/>
                            <button class="import-local">From Local</button>
                        </div>
                    </div>
                    <button class="add-note" title="Add document">+</button>
                </div>
            </div>
            <ul class="notes" ${cat.documents.length===0 ? 'style="display:none;"' : ''}>
                ${cat.documents.map(d => `<li class="doc-item" data-id="${d.id}" 
                    data-cat-id="${cat.id}"><span class="doc-name">${escapeHtml(d.title)}
                    </span></li>`).join('')}
            </ul>
        `;
        container.appendChild(categoryDiv);
    });
}

// Utility to escape HTML when injecting
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]; });
}

async function addDocumentToCategory(categoryId, title = "New Document", content = "") {
    try {
        // 1. Save to DATABASE first
        const response = await fetch('../backend/documents.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                title: title,
                content: content,
                category_id: categoryId
            })
        });

        const result = await response.json();
        
        if (!result.success) {
            alert(result.message || 'Error creating document');
            return null;
        }

        // 2. Get REAL database ID from server response
        const newDocument = {
            id: String(result.document.id), // ← REAL database ID
            title: result.document.title || title,
            content: result.document.content || content,
            updatedAt: result.document.updated_at || new Date().toISOString()
        };

        // 3. Update local data
        const cat = docSpaceData.categories.find(c => c.id === String(categoryId));
        if (cat) {
            cat.documents.push(newDocument);
            renderCategories();
            
            // Show documents list
            const catEl = document.querySelector(`.category[data-id="${categoryId}"] .notes`);
            if (catEl) catEl.style.display = 'block';
        }

        return newDocument;
        
    } catch (error) {
        console.error('Error creating document:', error);
        alert('Failed to create document');
        return null;
    }
}

async function importLocalFileToCategory(categoryId) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.txt, .md, .html, .docx, .pdf, text/*';
    
    input.addEventListener('change', async (ev) => {
        const file = ev.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = async function(e) {
            const content = e.target.result ?? '';
            
            // Clean the filename - remove problematic characters
            let cleanFileName = file.name
                .replace(/[^\w\s.-]/g, '') // Remove special chars except dots and dashes
                .trim();
            
            if (!cleanFileName) {
                cleanFileName = 'Imported Document';
            }
            
            console.log("Importing file:", file.name, "→ Cleaned:", cleanFileName);
            
            const newDoc = await addDocumentToCategory(
                categoryId, 
                cleanFileName,  // Use cleaned filename
                typeof content === 'string' ? content : ''
            );
            
            if (newDoc) {
                window.currentCategoryId = categoryId;
                loadDocument(newDoc.id);
            }
        };
        
        reader.readAsText(file);
    });
    
    input.click();
}
// Load a document into editor
function loadDocument(docId) {
    let doc = null;
    let parentCategory = null;

    for (const category of docSpaceData.categories) {
        const found = category.documents.find(d => d.id === docId);
        if (found) {
            doc = found;
            parentCategory = category;
            break;
        }
    }

    if (!doc) return;

    currentDocId = docId;
    window.currentCategoryId = parentCategory.id;

    // Save ONLY temporary data to localStorage
    localStorage.setItem('activeDocId', docId);
    localStorage.setItem('activeCategoryId', parentCategory.id);

    // GO TO EDITOR
    window.location.href = 'quill.php';
}
  

        // Event delegation for sidebar interactions
        document.getElementById("category-container")?.addEventListener("click", function (event) {
            console.log("Clicked element:", event.target);
    console.log("Has class 'category-name':", event.target.classList.contains('category-name'));
    
            // toggle documents list when clicking category header
            const header = event.target.closest('.category-header');
            if (header && !event.target.closest('button') && !event.target.closest('.import-menu')) {
                const notesEl = header.parentElement.querySelector('.notes');
                if (notesEl) {
                    const isHidden = notesEl.style.display === '' || notesEl.style.display === 'none';
                    notesEl.style.display = isHidden ? 'block' : 'none';
                    return;
                }
            }

            // add-note button to add new document (creates in model and opens editor)
            if (event.target.classList.contains('add-note')) {
                const categoryEl = event.target.closest(".category");
                if (!categoryEl) return;
                const cid = categoryEl.dataset.id;
               addDocumentToCategory(cid).then(newDoc => {
        if (newDoc) {
            window.currentCategoryId = cid;
            //loadDocument(newDoc.id);
        }
    });
                return;
            }

            let selectedDocItem = null;

document.getElementById("category-container")?.addEventListener("click", function (event) {
    const li = event.target.closest('.doc-item');
    if (!li) return;

    // 1. Highlight clicked document
    if (selectedDocItem) selectedDocItem.classList.remove('selected');
    li.classList.add('selected');
    selectedDocItem = li;

    // 2. Show action options (Open / Rename / Delete)
    showDocActions(li);
});

function showDocActions(li) {
    // Remove existing action menu if any
    document.querySelectorAll('.doc-action-menu').forEach(menu => menu.remove());

    const menu = document.createElement('div');
    menu.className = 'doc-action-menu';
    menu.style.position = 'absolute';
    menu.style.background = '#fff';
    menu.style.border = '1px solid #ccc';
    menu.style.padding = '5px';
    menu.style.zIndex = 1000;

    // Position menu near clicked li
    const rect = li.getBoundingClientRect();
    menu.style.top = `${rect.bottom + window.scrollY}px`;
    menu.style.left = `${rect.left + window.scrollX}px`;

    // Add buttons
    const openBtn = document.createElement('button');
    openBtn.innerText = 'Open';
    openBtn.onclick = () => {
        loadDocument(li.dataset.id);
        menu.remove();
    };

    const renameBtn = document.createElement('button');
    renameBtn.innerText = 'Rename';
    renameBtn.onclick = () => {
        renameDocument(li);
        menu.remove();
    };

    const deleteBtn = document.createElement('button');
    deleteBtn.innerText = 'Delete';
    deleteBtn.onclick = () => {
        deleteDocument(li);
        menu.remove();
    };

    menu.appendChild(openBtn);
    menu.appendChild(renameBtn);
    menu.appendChild(deleteBtn);

    document.body.appendChild(menu);
}




async function renameDocument(li) {
    const docId = li.dataset.id;
    const catId = li.dataset.catId;
    const currentTitle = li.querySelector('.doc-name')?.innerText || '';

    const newTitle = prompt('Rename document', currentTitle);
    if (!newTitle) return;

    const trimmedTitle = newTitle.trim();
    if (!trimmedTitle) {
        alert('Document title cannot be empty.');
        return;
    }

    try {
        // 1. Persist to server
        const response = await fetch('../backend/documents.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: docId,
                category_id: catId,
                title: trimmedTitle
            })
        });
        const result = await response.json();
        if (!result.success) {
            alert(result.message || 'Failed to rename document on server.');
            // Optional: reload data from server to rollback local changes
            await loadUserData();
            return;
        }

        // 2. Update local model
        for (const cat of docSpaceData.categories) {
            if (cat.id === catId) {
                const doc = cat.documents.find(d => d.id === docId);
                if (doc) doc.title = trimmedTitle;
            }
        }

        renderCategories();

    } catch (error) {
        console.error('Rename request failed:', error);
        alert('Network error. Could not rename document.');
        await loadUserData();
    }
}

// Function to delete a document permanently
async function deleteDocument(li) {
    const docId = li.dataset.id;
    const catId = li.dataset.catId;

    if (!confirm('This action cannot be undone. Continue?')) return;

    try {
        // 1. Call server to delete
        const response = await fetch(`documents.php?id=${docId}&category_id=${catId}`, {
            method: 'DELETE'
        });
        const result = await response.json();

        if (!result.success) {
            alert(result.message || 'Failed to delete on server.');
            return;
        }

        // 2. Update local model
        for (const cat of docSpaceData.categories) {
            const idx = cat.documents.findIndex(d => d.id === docId);
            if (idx !== -1) cat.documents.splice(idx, 1);
        }

        renderCategories();

        // 3. Clear editor if deleted document was open
        if (currentDocId === docId) {
            currentDocId = null;
            const editorEl = document.getElementById('editor');
            if (editorEl) editorEl.innerHTML = '';
        }

    } catch (error) {
        console.error('Delete request failed:', error);
        alert('Network error. Could not delete document.');
    }
}

            // import button (open import options)

            
            if (event.target.classList.contains('import-btn')) {
                const importMenu = event.target.closest('.import-menu');
                importMenu.classList.toggle('open');
                return;
            }

            // import options: google/local
            if (event.target.classList.contains('import-google')) {
                const catId = event.target.parentElement.dataset.catId;
                alert('Google import is not yet implemented in this demo.'); // placeholder
                event.target.closest('.import-menu')?.classList.remove('open');
                return;
            }
            if (event.target.classList.contains('import-local')) {
                const catId = event.target.parentElement.dataset.catId;
                importLocalFileToCategory(catId);
                event.target.closest('.import-menu')?.classList.remove('open');
                return;
            }

            // doc controls rename/delete
            if (event.target.classList.contains('rename')) {
                const li = event.target.closest('.doc-item');
                if (!li) return;
                const docId = li.dataset.id;
                const catId = li.dataset.catId || li.getAttribute('data-cat-id');

                const nameEl = li.querySelector('.doc-name');
                const currentTitle = nameEl ? nameEl.innerText : '';

                const newTitle = prompt('Rename document', currentTitle);
                if (newTitle === null) return; // cancelled

                const trimmedTitle = newTitle.trim();
                if (trimmedTitle === '') {
                    alert('Document title cannot be empty.');
                    return;
                }

                // Optimistic UI update
                let updatedLocally = false;
                for (const cat of docSpaceData.categories) {
                    if (String(cat.id) === String(catId)) {
                        const d = cat.documents.find(x => String(x.id) === String(docId));
                        if (d) { d.title = trimmedTitle; updatedLocally = true; break; }
                    }
                }
                if (updatedLocally) renderCategories();

                // Persist to server
                updateDocumentInDatabase(docId, catId, { title: trimmedTitle })
                    .then(result => {
                        if (!result || !result.success) {
                            console.error('Failed to update document on server:', result);
                            alert(result?.message || 'Failed to save document title to server. Changes will be reloaded.');
                            // rollback / re-sync from server
                            loadUserData();
                        } else {
                            // success: optionally refresh timestamps or fields from server
                            // if server returned updated document, merge its fields
                            if (result.document && result.document.id) {
                                for (const cat of docSpaceData.categories) {
                                    const d = cat.documents.find(x => String(x.id) === String(result.document.id));
                                    if (d) {
                                        d.title = result.document.title ?? d.title;
                                        d.updatedAt = result.document.updated_at ?? d.updatedAt;
                                        break;
                                    }
                                }
                                renderCategories();
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Update request failed:', err);
                        alert('Network error while renaming. Please try again.');
                        loadUserData();
                    });

                return;
            }
        });
////////////////////////////////////////////////////////
        // Toggle doc-controls showing on double-click of document item
       document.getElementById("category-container")?.addEventListener("dblclick", function (e) {
    // category name double-click to rename
    if (e.target.classList.contains('category-name')) {
        const catEl = e.target.closest('.category');
        if (!catEl) return;
        const cid = catEl.dataset.id;
        const newName = prompt('Rename category', e.target.textContent);
        
        if (newName !== null) {
            const trimmedName = newName.trim();
            if (!trimmedName) return; // Don't allow empty names
            
            const cat = docSpaceData.categories.find(c => c.id === cid);
            if (cat) { 
                // 1. Update local data
                cat.name = trimmedName;
                
                // 2. Update database
                updateCategoryInDatabase(cat).then(success => {
                    if (success) {
                        // 3. Update UI
                        renderCategories();
                    } else {
                        alert('Failed to save to database');
                       
                    }
                });
            }
        }
        return;
    }
    
    const li = e.target.closest('.doc-item');
            if (!li) return;
            // toggle controls visibility on that item
            li.classList.toggle('show-controls');
       
});

           // Fthe  function that updates category in database
async function updateCategoryInDatabase(category) {
    try {
        const response = await fetch('../backend/categories.php', {
            method: 'POST',   // changed from PUT
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update',  // tells backend this is a rename/update
                id: category.id,
                name: category.name
            })
        });
        const result = await response.json();
        return result.success;
    } catch (err) {
        console.error(err);
        return false;
    }
}


        // Click outside to hide any open import menus or doc controls
        document.addEventListener('click', function(e){
            document.querySelectorAll('.import-menu.open').forEach(m => {
                if (!m.contains(e.target)) m.classList.remove('open');
            });
            document.querySelectorAll('.doc-item.show-controls').forEach(i => {
                if (!i.contains(e.target) && !e.target.classList.contains('doc-controls')) i.classList.remove('show-controls');
            });
        });

        // Search documents (basic)
        document.getElementById('search-docs')?.addEventListener('input', function(e){
            const q = e.target.value.toLowerCase();
            document.querySelectorAll('.doc-item').forEach(li => {
                const name = li.querySelector('.doc-name')?.textContent.toLowerCase() ?? '';
                li.style.display = name.includes(q) ? '' : 'none';
            });
        });

        // add-category button
        document.getElementById("add-category")?.addEventListener("click", function () {
            const input = document.getElementById("NewCategoryName");
            if (!input) return;
            const categoryName = input.value.trim();
            if (!categoryName) return;
            addCategory(categoryName);
            input.value = "";
        });

////////////////////////////////////////////////////////////////////////////////////////////
                // Handle "Add Category" button click
document.getElementById('add-category-btn').addEventListener('click', async function () {
    const categoryName = document.getElementById('category-name-input').value.trim();

    if (!categoryName) {
        alert('Please enter a category name.');
        return;
    }

    try {
        const response = await fetch('../backend/categories.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ name: categoryName }),
        });

        const result = await response.json();

        if (result.success) {
            alert('Category created successfully!');
        } else {
            alert(`Error: ${result.message}`);
        }
    } catch (error) {
        console.error('Error creating category:', error);
        alert('An error occurred while creating the category. Please try again.');
    }
});

        // Add category
        async function addCategory(name) {
            if (!name || name.trim() === '') {
                alert('Category name cannot be empty.');
                return;
            }

            const newCategory = {name: name, documents: [] };

            try {
                // Send the new category to the database
                const response = await fetch('../backend/categories.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ name: name }),
                });

                const result = await response.json();
                console.log('Server response:', result); // Debugging: Log the server response

                if (result.success) {
                    // Use the category ID returned from the database
                    newCategory.id = result.category_id;
                    docSpaceData.categories.push(newCategory);
                    renderCategories(); // Re-render the categories
                    alert('Category created successfully!');
                } else {
                    throw new Error(result.message || 'Failed to create category in the database.');
                }
            } catch (error) {
                console.error('Error creating category:', error.message);
                alert('Failed to create category in the database. Saving locally as a backup.');

                // Fallback to localStorage
                docSpaceData.categories.push(newCategory);
                renderCategories();
            }
        }

// Load user data from server
async function loadUserData() {
    try {
        console.log("Loading user data from server...");
        
        // 1. CLEAR old data (start fresh)
        docSpaceData.categories = [];
        
        // 2. GET CATEGORIES from database
        const catResponse = await fetch('../backend/categories.php');
        const catData = await catResponse.json();
        
        if (!catData.success || !catData.categories) {
            throw new Error("No categories found");
        }
        
        console.log("Found categories:", catData.categories);
        
        // 3. FOR EACH CATEGORY, get its documents
        for (const dbCategory of catData.categories) {
            console.log(`Loading documents for category ${dbCategory.id}...`);
            
            const category = {
                id: String(dbCategory.id),       // REAL database ID
                name: dbCategory.name,           // Category name
                documents: []                    // Will fill with documents
            };
            
            // Ask server for documents in this category
            const docsResponse = await fetch(`../backend/documents.php?category_id=${dbCategory.id}`);
            const docsData = await docsResponse.json();
            
            // If we got documents, add them
            if (docsData.success && docsData.documents) {
                category.documents = docsData.documents.map(doc => ({
                    id: String(doc.id),          // Document's REAL ID
                    title: doc.title,
                    content: doc.content || '',
                    updatedAt: doc.updated_at
                }));
            }
            
            // Add completed category to our data
            docSpaceData.categories.push(category);
        }
        
        console.log("Final data structure:", docSpaceData);
        
        // 4. SHOW IT on screen
        renderCategories();
        
    } catch (error) {
        console.error("Failed to load:", error);
        alert("Could not load your data. Please refresh.");
    }}






















