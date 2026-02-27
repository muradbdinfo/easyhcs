import { useToast as useVueToastification } from 'vue-toastification'

/**
 * Thin wrapper around vue-toastification.
 * Import this everywhere instead of the plugin directly.
 *
 * Usage:
 *   const toast = useToast()
 *   toast.success('Saved!')
 *   toast.error('Something went wrong.')
 *   toast.warning('Stock is low.')
 *   toast.info('Processing...')
 */
export function useToast() {
  const toast = useVueToastification()

  return {
    success: (msg, opts = {}) =>
      toast.success(msg, { timeout: 3000, ...opts }),

    error: (msg, opts = {}) =>
      toast.error(msg, { timeout: 5000, ...opts }),

    warning: (msg, opts = {}) =>
      toast.warning(msg, { timeout: 4000, ...opts }),

    info: (msg, opts = {}) =>
      toast.info(msg, { timeout: 3000, ...opts }),
  }
}