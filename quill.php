<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Docs Clone - Full Editor</title>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <style>
        /* --- 1. GLOBAL LAYOUT --- */
        body {
            background-color: #F8F9FA;
            margin: 0;
            padding: 0;
            font-family: 'Roboto', 'Arial', sans-serif;
        }

        /* --- 2. ENHANCED TOOLBAR --- */
        #toolbar-container {
            background-color: #edf2fa;
            border-bottom: 1px solid #c7c7c7;
            padding: 5px;
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        #toolbar {
            border: none !important;
            max-width: 1100px;
        }

        /* --- 3. EDITOR / PAGE LAYOUT --- */
        #editor-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            padding-bottom: 60px;
        }

        #editor {
            background-color: white;
            width: 210mm;
            min-height: 297mm;
            padding: 20mm 25mm;
            /* Standard Margins */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            border: 1px solid #c7c7c7;
            font-size: 11pt;
            line-height: 1.6;
        }

        /* --- 4. AUTOMATIC PAGE CREATION & NUMBERING --- */
        .ql-editor {
            padding: 0 !important;
            overflow-y: visible !important;
            height: auto !important;
            /* Creates the visual gap every A4 page height (approx 1123px) */
            background-image: linear-gradient(#F8F9FA 15px, transparent 15px);
            background-size: 100% 1123px;
            background-repeat: repeat-y;
        }

        /* Page Number Display */
        #page-info {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 2000;
        }

        /* Real Page Break Styling */
        .page-break {
            border: none;
            border-bottom: 2px dashed #4285f4;
            height: 1px;
            margin: 30px 0;
            display: block;
        }

        @media print {
            body {
                background: white;
            }

            #toolbar-container,
            #page-info {
                display: none;
            }

            #editor {
                box-shadow: none;
                border: none;
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .page-break {
                page-break-after: always;
                visibility: hidden;
            }
        }

        #save-doc-btn,
        #home-btn {
            border: none;
            background: #4a90e2;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }

        #home-btn {
            background: #6b7280;
        }

        #save-doc-btn:hover {
            background: #357ABD;
        }

        #home-btn:hover {
            background: #4b5563;
        }

        /* Category and Document List Styles */
        #category-list,
        #document-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .category-item,
        .document-item {
            padding: 10px;
            margin: 5px 0;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .category-item:hover,
        .document-item:hover {
            background: #f1f1f1;
        }

        .active-category {
            background: #d1e7dd;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div id="toolbar-container">
        <div id="toolbar">
            <span class="ql-formats">
                <button id="save-doc-btn" title="Save Document">💾 Save</button>
                <button id="home-btn" title="Back to Home">🏠 Home</button>
            </span>

            <span class="ql-formats">
                <select class="ql-header">
                    <option selected>Normal</option>
                    <option value="1">Heading 1</option>
                    <option value="2">Heading 2</option>
                </select>
                <select class="ql-font"></select>
            </span>

            <span class="ql-formats">
                <button class="ql-bold"></button>
                <button class="ql-italic"></button>
                <button class="ql-underline"></button>
                <button class="ql-strike"></button>
            </span>

            <span class="ql-formats">
                <select class="ql-color"></select>
                <select class="ql-background"></select>
            </span>

            <span class="ql-formats">
                <button class="ql-script" value="sub"></button>
                <button class="ql-script" value="super"></button>
            </span>

            <span class="ql-formats">
                <button class="ql-list" value="ordered"></button>
                <button class="ql-list" value="bullet"></button>
                <select class="ql-align"></select>
            </span>

            <span class="ql-formats">
                <button class="ql-link"></button>
                <button class="ql-image"></button>
                <button class="ql-video"></button>
                <button class="ql-formula"></button>
                <button class="ql-code-block"></button>
                <button id="insert-page-break" title="Insert Page Break">
                    <svg viewBox="0 0 24 24" width="18" height="18">
                        <path fill="currentColor" d="M3 11h18v2H3v-2m0-4h18v2H3V7m0 8h18v2H3v-2m0 4h18v2H3v-2Z" />
                    </svg>
                </button>
            </span>

            <span class="ql-formats">
                <button class="ql-clean"></button>
            </span>

        </div>
    </div>

    <div id="page-info">Page 1</div>

    <div id="editor-container">
        <div id="editor">
            <h1 style="text-align: center;">New Document</h1>
            <p><br></p>
            <p>Start typing to create content. When you reach the end of the sheet, the background will show a gray
                break, simulating a new page.</p>
        </div>
    </div>

    <!-- Category and Document Lists -->
    <div id="sidebar">
        <div>
            <h2>Categories</h2>
            <div id="category-list"></div>
        </div>
        <div>
            <h2>Documents</h2>
            <div id="document-list"></div>
        </div>
    </div>
    <script>
    // 1. Register Page Break Blot
    let BlockEmbed = Quill.import('blots/block/embed');
    class PageBreakBlot extends BlockEmbed { }
    PageBreakBlot.blotName = 'pagebreak';
    PageBreakBlot.tagName = 'hr';
    PageBreakBlot.className = 'page-break';
    Quill.register(PageBreakBlot);

    // 2. Initialize Editor
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: '#toolbar'
        },
        placeholder: 'Write your masterpiece...'
    });

    // 3. Page Break Handler
    document.getElementById('insert-page-break').addEventListener('click', function () {
        const range = quill.getSelection();
        if (range) {
            quill.insertEmbed(range.index, 'pagebreak', true);
            quill.setSelection(range.index + 1);
        }
    });

    // 4. Page Numbering Logic
    function updatePageStats() {
        const editorElement = document.querySelector('.ql-editor');
        const scrollHeight = editorElement.scrollHeight;
        const pageHeight = 1123;

        const totalPages = Math.ceil(scrollHeight / pageHeight);
        const range = quill.getSelection();
        let currentPage = 1;
        if (range) {
            const bounds = quill.getBounds(range.index);
            currentPage = Math.ceil((bounds.top + editorElement.scrollTop) / pageHeight) || 1;
        }

        document.getElementById('page-info').innerText = `Page ${currentPage} of ${totalPages}`;
        document.getElementById('editor').style.minHeight = (totalPages * pageHeight) + "px";
    }

    quill.on('text-change', updatePageStats);
    quill.on('selection-change', updatePageStats);

    // 5. Get temporary data from localStorage
    const activeDocId = localStorage.getItem('activeDocId');
    const activeCategoryId = localStorage.getItem('activeCategoryId');
    
    console.log("Loading document:", activeDocId, "from category:", activeCategoryId);

    // 6. Load document from DATABASE (not localStorage)
    async function loadDocumentFromDatabase() {
        if (!activeDocId || !activeCategoryId) {
            alert('No document selected. Please go back to main page.');
            return;
        }

        try {
            console.log("Fetching from database...");
            const response = await fetch(`documents.php?id=${activeDocId}&category_id=${activeCategoryId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const result = await response.json();
            console.log("Database response:", result);
            
            if (result.success && result.document) {
                const doc = result.document;
                
                // Set title
                document.title = doc.title || 'Untitled Document';
                
                // Load content into editor
                if (doc.content) {
                    quill.clipboard.dangerouslyPasteHTML(doc.content);
                } else {
                    quill.setText(''); // Empty document
                }
                
                console.log("Document loaded from database successfully");
            } else {
                throw new Error(result.message || 'Document not found in database');
            }
        } catch (error) {
            console.error("Database load failed:", error);
            alert('Could not load document from database. Please try again.');
            
            // Optional: Clear localStorage since it failed
            localStorage.removeItem('activeDocId');
            localStorage.removeItem('activeCategoryId');
        }
    }

    // 7. Save document to DATABASE
    async function saveDocumentToDatabase() {
        if (!activeDocId || !activeCategoryId) {
            alert('No active document to save.');
            return;
        }

        const content = quill.root.innerHTML;
        const title = document.title || 'Untitled Document';

        console.log("Saving to database...", { 
            id: activeDocId, 
            category_id: activeCategoryId,
            title: title,
            content_length: content.length 
        });

        try {
            const response = await fetch('documents.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: activeDocId,
                    category_id: activeCategoryId,
                    title: title,
                    content: content
                })
            });

            const responseText = await response.text();
            console.log("Save response:", responseText);
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                throw new Error('Invalid JSON from server: ' + responseText.substring(0, 100));
            }

            if (result.success) {
                alert('✅ Document saved to database!');
                return true;
            } else {
                throw new Error(result.message || 'Save failed');
            }
        } catch (error) {
            console.error('Database save failed:', error);
            alert('❌ Save failed: ' + error.message);
            return false;
        }
    }

    // 8. Optional: Auto-save draft to localStorage (temporary backup)
    let autoSaveInterval;
    function startAutoSaveDraft() {
        // Save draft every 30 seconds
        autoSaveInterval = setInterval(() => {
            const draft = {
                content: quill.root.innerHTML,
                title: document.title,
                savedAt: new Date().toISOString()
            };
            localStorage.setItem(`draft_${activeDocId}`, JSON.stringify(draft));
            console.log("Auto-saved draft");
        }, 30000); // 30 seconds
    }

    function checkForDraft() {
        const draftData = localStorage.getItem(`draft_${activeDocId}`);
        if (draftData) {
            const draft = JSON.parse(draftData);
            const draftAge = new Date() - new Date(draft.savedAt);
            const minutesOld = Math.floor(draftAge / 60000);
            
            if (minutesOld < 5) { // Draft less than 5 minutes old
                if (confirm(`Found unsaved draft from ${minutesOld} minute(s) ago. Load it?`)) {
                    quill.clipboard.dangerouslyPasteHTML(draft.content);
                    document.title = draft.title;
                }
            }
            
            // Clear the draft after offering
            localStorage.removeItem(`draft_${activeDocId}`);
        }
    }

    // 9. Button Event Listeners
    document.getElementById('save-doc-btn').addEventListener('click', async function () {
        const saved = await saveDocumentToDatabase();
        if (saved) {
            // Clear any auto-save draft since we saved to database
            localStorage.removeItem(`draft_${activeDocId}`);
        }
    });

    document.getElementById('home-btn').addEventListener('click', function () {
        // Optional: Ask to save before leaving
        if (confirm('Save before going home?')) {
            saveDocumentToDatabase().then(() => {
                window.location.href = 'main_page.php';
            });
        } else {
            window.location.href = 'main_page.php';
        }
    });

    // 10. Initialize everything when page loads
    document.addEventListener('DOMContentLoaded', function () {
        // Load the document from database
        loadDocumentFromDatabase().then(() => {
            // Check for unsaved drafts
            checkForDraft();
            
            // Start auto-saving drafts
            startAutoSaveDraft();
            
            // Initial page stats
            updatePageStats();
        });
    });

    // 11. Clean up on page unload
    window.addEventListener('beforeunload', function () {
        if (autoSaveInterval) {
            clearInterval(autoSaveInterval);
        }
    });
</script>
</body>

</html>