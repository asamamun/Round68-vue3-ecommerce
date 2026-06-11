// src/utils/toast.js
import { toast } from 'vue3-toastify'

const config = {
  position: 'top-right',
  autoClose: 3000,
  closeButton: true,
  pauseOnHover: true,
  draggable: true,
  theme: 'light'
}

export const showToast = {
  success: (message) => {
    toast.success(message, {
      ...config,
      autoClose: 2500
    })
  },
  error: (message) => {
    toast.error(message, {
      ...config,
      autoClose: 3500
    })
  },
  info: (message) => {
    toast.info(message, {
      ...config,
      autoClose: 2500
    })
  },
  warning: (message) => {
    toast.warning(message, {
      ...config,
      autoClose: 3000
    })
  },
  loading: (message) => {
    toast.loading(message, {
      ...config,
      autoClose: false
    })
  }
}
