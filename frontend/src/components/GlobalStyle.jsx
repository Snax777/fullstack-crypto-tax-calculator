import { createGlobalStyle } from 'styled-components';

const GlobalStyle = createGlobalStyle`
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    :root {
        /* TaxTim Brand Colors */
        --primary-teal: #00BCD4;
        --primary-teal-light: #E0F7FA;
        --primary-teal-dark: #0097A7;
        
        --accent-coral: #FF6B6B;
        --accent-coral-hover: #FF5252;
        
        /* Modern Neutrals */
        --gray-50: #FAFAFA;
        --gray-100: #F5F5F5;
        --gray-200: #EEEEEE;
        --gray-300: #E0E0E0;
        --gray-400: #BDBDBD;
        --gray-500: #9E9E9E;
        --gray-600: #757575;
        --gray-700: #616161;
        --gray-800: #424242;
        --gray-900: #212121;
        
        /* Status Colors */
        --success: #4CAF50;
        --error: #F44336;
        --warning: #FFC107;
        
        --white: #FFFFFF;
        --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 6px rgba(0,0,0,0.07);
        --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
        
        --radius-sm: 6px;
        --radius-md: 8px;
        --radius-lg: 12px;
        --radius-xl: 16px;
    }
    
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #E0F7FA 0%, #F5F5F5 100%);
        color: var(--gray-900);
        line-height: 1.6;
        min-height: 100vh;
    }
`;

export default GlobalStyle;
