import { createStore } from 'vuex'

const persistCart = (state) => localStorage.setItem('cart', JSON.stringify(state.cart))

export default createStore({
  state: {
    token: localStorage.getItem('token') || null,
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    // รายการในตะกร้า: { product_id, name, price, image, size, quantity, stock }
    cart: JSON.parse(localStorage.getItem('cart') || '[]'),
  },
  getters: {
    isLoggedIn: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
    cartCount: (state) => state.cart.reduce((sum, i) => sum + i.quantity, 0),
    cartTotal: (state) => state.cart.reduce((sum, i) => sum + i.price * i.quantity, 0),
  },
  mutations: {
    setAuth(state, { token, user }) {
      state.token = token
      state.user = user
      localStorage.setItem('token', token)
      localStorage.setItem('user', JSON.stringify(user))
    },
    setUser(state, user) {
      state.user = user
      localStorage.setItem('user', JSON.stringify(user))
    },
    logout(state) {
      state.token = null
      state.user = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    },

    addToCart(state, item) {
      const found = state.cart.find((i) => i.product_id === item.product_id && i.size === item.size)
      if (found) {
        found.quantity = Math.min(found.quantity + item.quantity, item.stock)
        found.stock = item.stock
      } else {
        state.cart.push({ ...item, quantity: Math.min(item.quantity, item.stock) })
      }
      persistCart(state)
    },
    setCartQty(state, { index, quantity }) {
      const item = state.cart[index]
      if (!item) return
      item.quantity = Math.max(1, Math.min(quantity, item.stock))
      persistCart(state)
    },
    removeFromCart(state, index) {
      state.cart.splice(index, 1)
      persistCart(state)
    },
    clearCart(state) {
      state.cart = []
      persistCart(state)
    },
  },
})