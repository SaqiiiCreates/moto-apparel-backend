<script src="/hugerte/hugerte.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    setTimeout(() => {
        document.querySelectorAll('.huge-editor-target').forEach((el) => {

            hugerte.init({
                target: el,
                height: 450,
                menubar: false,
                base_url: '/hugerte',
                suffix: '.min',

                plugins: 'code lists link autoresize charmap emoticons wordcount',
                toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link emoticons | code',

                setup: function (editor) {
                    editor.on('init', function () {

                        const container = editor.getContainer();
                        if (container) {
                            container.style.visibility = 'visible';
                            container.style.opacity = '1';
                        }

                        setTimeout(() => {
                            editor.theme.resizeTo(null, 450);
                        }, 100);

                        const textarea = document.getElementById(editor.id);
                        if (textarea && textarea.value.trim() !== '') {
                            editor.setContent(textarea.value);
                        } else {
                            editor.setContent('<p></p>');
                        }
                    });
                }
            });

        });

    }, 300);

});


async function saveSection(btn, pageId, sectionId) {

    const editorInstance = hugerte.get(`huge-${sectionId}`);
    if (!editorInstance) return;

    const content = editorInstance.getContent();
    const originalText = btn.innerText;

    btn.innerText = "SAVING...";
    btn.disabled = true;

    try {
        const res = await fetch(`/admin/pages/${pageId}/sections/${sectionId}`, {
            method: "PUT",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Accept": "application/json",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ content })
        });

        if (!res.ok) throw new Error();

        btn.innerText = "✓ SAVED";
        btn.classList.replace('text-blue-600', 'text-green-600');

        setTimeout(() => {
            btn.innerText = originalText;
            btn.classList.replace('text-green-600', 'text-blue-600');
            btn.disabled = false;
        }, 2000);

    } catch (e) {
        console.error(e);
        btn.innerText = "RETRY";
        btn.disabled = false;
    }
}
</script><script src="/hugerte/hugerte.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    setTimeout(() => {
        document.querySelectorAll('.huge-editor-target').forEach((el) => {

            hugerte.init({
                target: el,
                height: 450,
                menubar: false,
                base_url: '/hugerte',
                suffix: '.min',

                plugins: 'code lists link autoresize charmap emoticons wordcount',
                toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link emoticons | code',

                setup: function (editor) {
                    editor.on('init', function () {

                        const container = editor.getContainer();
                        if (container) {
                            container.style.visibility = 'visible';
                            container.style.opacity = '1';
                        }

                        setTimeout(() => {
                            editor.theme.resizeTo(null, 450);
                        }, 100);

                        const textarea = document.getElementById(editor.id);
                        if (textarea && textarea.value.trim() !== '') {
                            editor.setContent(textarea.value);
                        } else {
                            editor.setContent('<p></p>');
                        }
                    });
                }
            });

        });

    }, 300);

});


async function saveSection(btn, pageId, sectionId) {

    const editorInstance = hugerte.get(`huge-${sectionId}`);
    if (!editorInstance) return;

    const content = editorInstance.getContent();
    const originalText = btn.innerText;

    btn.innerText = "SAVING...";
    btn.disabled = true;

    try {
        const res = await fetch(`/admin/pages/${pageId}/sections/${sectionId}`, {
            method: "PUT",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Accept": "application/json",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ content })
        });

        if (!res.ok) throw new Error();

        btn.innerText = "✓ SAVED";
        btn.classList.replace('text-blue-600', 'text-green-600');

        setTimeout(() => {
            btn.innerText = originalText;
            btn.classList.replace('text-green-600', 'text-blue-600');
            btn.disabled = false;
        }, 2000);

    } catch (e) {
        console.error(e);
        btn.innerText = "RETRY";
        btn.disabled = false;
    }
}
async function addNewSection(pageId) {
    const btn = event.currentTarget;
    const originalText = btn.innerHTML;
    
    btn.innerText = "ADDING...";
    btn.disabled = true;

    try {
        const res = await fetch(`/admin/pages/${pageId}/sections`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Accept": "application/json",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ 
                type: 'content',
                content: '' 
            })
        });

        if (!res.ok) throw new Error();

        // Reload the page to initialize the new HugeRTE editor for the new section
        window.location.reload();

    } catch (e) {
        console.error(e);
        alert("Failed to add section.");
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}
</script>