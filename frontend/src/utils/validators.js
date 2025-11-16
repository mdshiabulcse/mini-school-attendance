export const required = (value) => !!value || 'This field is required'

export const email = (value) => {
  const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return pattern.test(value) || 'Invalid email address'
}

export const minLength = (min) => (value) =>
  value.length >= min || `Must be at least ${min} characters`

export const maxLength = (max) => (value) =>
  value.length <= max || `Must be less than ${max} characters`

export const phone = (value) => {
  const pattern = /^[\+]?[1-9][\d]{0,15}$/
  return pattern.test(value) || 'Invalid phone number'
}

export const password = (value) => {
  const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/
  return pattern.test(value) || 'Password must contain uppercase, lowercase, number and be at least 8 characters'
}
