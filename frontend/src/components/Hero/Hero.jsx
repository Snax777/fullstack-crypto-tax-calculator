import React from 'react';
import styled from 'styled-components';

const Hero = () => {
  return (
    <Container>
      <div className='wrapper'>
        <div className='badge'>SARS Approved</div>
        <h1>Crypto Tax Calculator</h1>
        <p>
          Calculate your cryptocurrency capital gains tax using the South
          African Revenue Service approved First-In-First-Out method
        </p>
      </div>
    </Container>
  );
};

const Container = styled.div`
  .badge {
    color: var(--primary-teal-dark);
    background-color: var(--primary-teal-light);
    display: inline-block;
    margin-inline: auto;
    padding: 0.25rem 0.75rem;
    borader ra
  }

  .wrapper{
    max-width:1200px;
    margin: 0 auto;
  }

  h1{
    text-align: center;
  }
`;

export default Hero;
