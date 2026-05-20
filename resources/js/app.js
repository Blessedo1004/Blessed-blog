import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'

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
                        ],
                        content: this.content,
                        onUpdate: ({ editor: e }) => {
                            // Update the local Alpine property
                            this.content = e.getHTML();
                        },
                    });
                } catch (error) {
                    console.error('Tiptap: Initialization failed', error);
                }
            });

            // Cleanup
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
