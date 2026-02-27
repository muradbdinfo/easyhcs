import { useToast as useVueToastification } from 'vue-toastification';

/**
 * EasyHCS toast composable.
 * Always import from '@/composables/useToast' — never import directly from 'vue-toastification'.
 * This allows us to swap the toast library in one place if needed.
 */
export function useToast() {
    const toast = useVueToastification();

    return {
        success: (message, options = {}) => toast.success(message, options),
        error:   (message, options = {}) => toast.error(message, options),
        warning: (message, options = {}) => toast.warning(message, options),
        info:    (message, options = {}) => toast.info(message, options),

        // Shorthand for async operations
        promise: (promise, messages = {}) => toast.promise(promise, {
            pending: messages.pending ?? 'Processing...',
            success: messages.success ?? 'Done!',
            error:   messages.error   ?? 'Something went wrong.',
        }),
    };
}