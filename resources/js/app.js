import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import Link from '@tiptap/extension-link'

window.setupEditor = (initialContent) => {
    let editor;

    return {
        content: initialContent,

        init(element) {
            this.$nextTick(() => {
                if (editor) {
                    editor.destroy();
                }

                try {
                    editor = new Editor({
                        element: element,
                        extensions: [
                            StarterKit,
                            Image.configure({
                                HTMLAttributes: {
                                    class: 'max-w-full h-auto rounded-lg my-4',
                                },
                            }),
                            Link.configure({
                                openOnClick: false,
                                HTMLAttributes: {
                                    class: 'text-indigo-600 underline cursor-pointer',
                                },
                            }),
                        ],
                        content: this.content,
                        onUpdate: ({ editor: e }) => {
                            this.content = e.getHTML();
                        },
                    });
                } catch (error) {
                    console.error('Tiptap: Initialization failed', error);
                }
            });

            return () => {
                if (editor) {
                    editor.destroy();
                    editor = null;
                }
            };
        },

        isActive(type, opts = {}) {
            return editor ? editor.isActive(type, opts) : false;
        },

        // Link Support
        setLink() {
            if (!editor) return;
            
            // 1. Check if there's an existing link
            const previousUrl = editor.getAttributes('link').href;
            
            // 2. Get the currently selected text
            const { from, to } = editor.state.selection;
            const selectedText = editor.state.doc.textBetween(from, to, ' ');
            
            // 3. Smart suggestion: use existing link OR selected text if it looks like a URL
            const isUrl = (str) => /^(https?:\/\/|www\.)[^\s]+$/.test(str);
            const defaultUrl = previousUrl || (isUrl(selectedText) ? (selectedText.startsWith('www.') ? 'https://' + selectedText : selectedText) : '');

            const url = window.prompt('URL', defaultUrl);

            // cancelled
            if (url === null) {
                return;
            }

            // empty
            if (url === '') {
                editor.chain().focus().extendMarkRange('link').unsetLink().run();
                return;
            }

            // update link
            editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
        },

        // Image Support (Upload)
        addImage() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';

            input.onchange = async (_) => {
                const file = input.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('image', file);

                try {
                    const response = await fetch('/tiptap/upload', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Upload failed');
                    }

                    const data = await response.json();
                    
                    if (data.url && editor) {
                        editor.chain().focus().setImage({ src: data.url }).run();
                    }
                } catch (error) {
                    console.error('Tiptap: Image upload error', error);
                    alert('Image upload failed: ' + error.message);
                }
            };

            input.click();
        },

        // Image Support (URL)
        addImageUrl() {
            const url = window.prompt('Enter the image URL:');
            if (url && editor) {
                editor.chain().focus().setImage({ src: url }).run();
            }
        },

        // Formatting
        toggleBold() { if (editor) editor.chain().focus().toggleBold().run(); },
        toggleItalic() { if (editor) editor.chain().focus().toggleItalic().run(); },
        toggleStrike() { if (editor) editor.chain().focus().toggleStrike().run(); },
        toggleCode() { if (editor) editor.chain().focus().toggleCode().run(); },
        toggleHeading(level) { if (editor) editor.chain().focus().toggleHeading({ level }).run(); },
        toggleBulletList() { if (editor) editor.chain().focus().toggleBulletList().run(); },
        toggleOrderedList() { if (editor) editor.chain().focus().toggleOrderedList().run(); },
        toggleBlockquote() { if (editor) editor.chain().focus().toggleBlockquote().run(); },
        toggleCodeBlock() { if (editor) editor.chain().focus().toggleCodeBlock().run(); },
        setHorizontalRule() { if (editor) editor.chain().focus().setHorizontalRule().run(); },
        undo() { if (editor) editor.chain().focus().undo().run(); },
        redo() { if (editor) editor.chain().focus().redo().run(); },
    }
}
