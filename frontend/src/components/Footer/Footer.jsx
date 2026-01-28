import React from 'react';
import styled from 'styled-components';

const Footer = () => {
  return (
    <Container className='footer'>
      <div className='footer-content'>
        <p>
          &copy; 2026 TaxTim. All rights reserved. | This calculator uses the
          SARS-approved FIFO method for crypto tax calculations.
        </p>
      </div>
    </Container>
  );
};

const Container = styled.footer`
  background: var(--gray-900);
  color: var(--gray-400);
  padding: 2rem 0;
  margin-top: 4rem;
  text-align: center;

  .footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
  }
`;

export default Footer;
