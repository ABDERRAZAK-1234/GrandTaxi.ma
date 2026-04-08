import api from './api'

export const login = async (email, password) => {
  const response = await api.post('/api/login', { email, password })
  const token = response.data.access_token
  localStorage.setItem('token', token)
  return response.data
}

export const logout = async () => {
  await api.post('/api/logout')
  localStorage.removeItem('token')
}

export const getUser = async () => {
  const response = await api.get('/api/user')
  return response.data
}
